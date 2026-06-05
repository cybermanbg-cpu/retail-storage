<?php

namespace App\Http\Controllers;

use App\Helpers\CartHelper;
use App\Http\Controllers\Controller;
use App\Models\ActiveCart;
use App\Models\Category;
use App\Models\Client;
use App\Models\Owner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Stock;
use App\Models\StorageObject;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $user = Auth::user();

        // Вземаме складовия обект на потребителя
        $storageObject = $this->getCurrentStorageObject();

        if (!$storageObject) {
            return redirect()->back()->with('error', 'Нямате асоцииран складов обект. Моля, свържете се с администратор.');
        }

        // Вземаме всички активни продукти за този склад
        $products = Product::with(['variants.color', 'variants.size', 'unitOfMeasure'])
            ->where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $user->owner_id ?? $this->getCurrentOwnerId())
            ->get();

        $clients = Client::where('is_active', true)
            ->where('owner_id', $user->owner_id ?? $this->getCurrentOwnerId())
            ->get();

        $activeCarts = $this->cartService->getActiveCarts();
        $currentCart = $activeCarts->first();

        if (!$currentCart) {
            $currentCart = $this->cartService->createCart($storageObject->id);
            $activeCarts = collect([$currentCart]);
        }

        return view('pos.index', compact('products', 'clients', 'storageObject', 'activeCarts', 'currentCart'));
    }

    public function createNewCart(Request $request)
    {
        try {
            $cart = $this->cartService->createCart($request->storage_object_id);
            return response()->json(['success' => true, 'cart' => $cart]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCart($cartId)
    {
        try {
            $cart = $this->cartService->getCart($cartId);
            return response()->json($cart);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getCarts()
    {
        return response()->json($this->cartService->getActiveCarts());
    }

    public function addToCart(Request $request, $cartId)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $result = $this->cartService->addItem($cartId, $request->variant_id, $request->quantity ?? 1);

        return response()->json($result);
    }

    public function removeFromCart(Request $request, $cartId)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $result = $this->cartService->removeItem($cartId, $request->index);

        return response()->json($result);
    }

    public function updateCart(Request $request, $cartId)
    {
        $cart = $this->cartService->updateCart($cartId, $request->only(['items', 'client_id', 'cart_name']));
        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function getVariantStock(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'storage_object_id' => 'required|exists:storage_objects,id',
        ]);

        $stockCheck = CartHelper::checkStock($request->variant_id, $request->storage_object_id);

        return response()->json($stockCheck);
    }

    public function createReceipt(Request $request)
    {
        try {
            DB::beginTransaction();

            // Вземаме складовия обект на потребителя
            $storageObject = $this->getCurrentStorageObject();

            if (!$storageObject) {
                return response()->json(['success' => false, 'message' => 'Нямате асоцииран складов обект. Моля, свържете се с администратор.'], 400);
            }

            $data = $request->validate([
                'cart_id' => 'required|exists:active_carts,id',
                'client_id' => 'nullable|exists:clients,id',
                'payment_method' => 'required|in:cash,card,bank_transfer',
                'amount_paid' => 'required|numeric|min:0',
                'change_amount' => 'nullable|numeric|min:0',
                'items' => 'required|array',
                'items.*.variant_id' => 'required|exists:product_variants,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            // Използваме складовия обект на потребителя
            $data['storage_object_id'] = $storageObject->id;

            $cart = ActiveCart::where('user_id', Auth::id())
                ->where('id', $data['cart_id'])
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Количката не беше намерена'
                ], 404);
            }

            $receiptNumber = 'R-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $totalVat = 0;

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                // Превръщане в float за гарантиране на коректен тип
                $price = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);
                $vatRate = floatval($variant->product->vat_rate);
                $vatAmount = ($price * $vatRate / 100) * $quantity;

                $totalAmount += $price * $quantity;
                $totalVat += $vatAmount;
            }

            $receipt = Receipt::create([
                'owner_id' => $cart->owner_id,
                'storage_object_id' => intval($data['storage_object_id']),
                'client_id' => $data['client_id'] ?? null,
                'user_id' => Auth::id(),
                'receipt_number' => $receiptNumber,
                'type' => 'sale',
                'total_amount' => floatval($totalAmount),
                'total_vat' => floatval($totalVat),
                'payment_method' => $data['payment_method'],
                'amount_paid' => floatval($data['amount_paid']),
                'change_amount' => floatval($data['change_amount'] ?? 0),
                'notes' => $request->get('notes'),
                'is_invoiced' => false,
            ]);

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                $quantity = intval($item['quantity']);
                $unitPrice = floatval($item['unit_price']);

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'product_variant_id' => intval($item['variant_id']),
                    'product_name_snapshot' => $variant->product->name,
                    'sku_snapshot' => $variant->full_sku,
                    'color_name' => $variant->color?->name,
                    'size_name' => $variant->size?->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'vat_rate' => floatval($variant->product->vat_rate),
                    'total' => $unitPrice * $quantity,
                    'unit_of_measure_snapshot' => $variant->product->unitOfMeasure?->symbol ?? 'бр.',
                    'decimal_places_snapshot' => $variant->product->unitOfMeasure?->decimal_places ?? 0,

                ]);

                $stock = Stock::where('product_variant_id', intval($item['variant_id']))
                    ->where('storage_object_id', intval($data['storage_object_id']))
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $quantity);
                }
            }

            $cart->status = 'completed';
            $cart->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
                'receipt_number' => $receiptNumber,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Receipt creation error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // ⬇️ Добави 'unitOfMeasure' тук ⬇️
        $products = Product::with(['variants.color', 'variants.size', 'barcodes', 'unitOfMeasure'])
            ->where('type', 'product')
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('barcodes', function ($q) use ($search) {
                        $q->where('barcode', 'like', "%{$search}%");
                    });
            })
            ->limit(50)
            ->get();

        return response()->json($products);
    }

    public function deleteCart($cartId)
    {
        try {
            $this->cartService->deleteCart($cartId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Добавяне на продукт без вариант (виртуален) в количката
     */
    public function addToCartVirtual(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'storage_object_id' => 'required|exists:storage_objects,id',
                'quantity' => 'required|numeric|min:0.001',
                'unit_price' => 'required|numeric|min:0',
                'unit' => 'nullable|string',
                'decimal_places' => 'nullable|integer',
            ]);

            // Намери или създай вариант за продукта
            $variant = ProductVariant::firstOrCreate(
                ['product_id' => $request->product_id],
                [
                    'color_id' => null,
                    'size_id' => null,
                    'sku_suffix' => null,
                    'price_adjustment' => 0,
                    'is_active' => true,
                ]
            );

            // Вземаме активната количка на потребителя
            $activeCarts = $this->cartService->getActiveCarts();
            $cart = $activeCarts->first();

            if (!$cart) {
                $storageObject = StorageObject::find($request->storage_object_id);
                $cart = $this->cartService->createCart($storageObject->id);
            }

            // Добавяне в количката
            $result = $this->cartService->addItemWithDetails(
                $cart->id,
                $variant->id,
                $request->quantity,
                $request->unit_price,
                $request->unit ?? 'бр.',
                $request->decimal_places ?? 0
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurant POS изглед
     */
    /**
     * Restaurant POS изглед
     */
    public function restaurantPos()
    {
        $user = Auth::user();

        // Вземаме складовия обект на потребителя
        $storageObject = $this->getCurrentStorageObject();

        if (!$storageObject) {
            return redirect()->back()->with('error', 'Нямате асоцииран складов обект. Моля, свържете се с администратор.');
        }

        $categories = Category::where('is_active', true)
            ->where('show_in_restaurant_pos', true)
            ->where('owner_id', $user->owner_id ?? $this->getCurrentOwnerId())
            ->orderBy('sort_order')
            ->get();

        $clients = Client::where('is_active', true)
            ->where('owner_id', $user->owner_id ?? $this->getCurrentOwnerId())
            ->get();

        // Вземаме активните колички за ресторанта
        $activeCarts = $this->cartService->getActiveCarts();
        $currentCart = $activeCarts->first();

        if (!$currentCart) {
            $currentCart = $this->cartService->createCart($storageObject->id);
        }

        return view('pos.restaurant', compact('categories', 'clients', 'storageObject', 'activeCarts', 'currentCart'));
    }

    /**
     * Продукти по категория (за Restaurant POS)
     */
    public function productsByCategory($categoryId)
    {
        $user = Auth::user();

        // Вземаме складовия обект на потребителя
        $storageObject = $this->getCurrentStorageObject();

        if (!$storageObject) {
            return response()->json([]);
        }

        $category = Category::with('products')->findOrFail($categoryId);
        $storageObjectId = $storageObject->id;

        $products = $category->products->filter(function ($product) use ($storageObjectId) {
            $variant = $product->variants()->first();
            if (!$variant)
                return false;

            $stock = Stock::where('product_variant_id', $variant->id)
                ->where('storage_object_id', $storageObjectId)
                ->first();

            return $stock && $stock->quantity > 0;
        })->map(function ($product) use ($storageObjectId) {
            $variant = $product->variants()->first();
            $stock = Stock::where('product_variant_id', $variant->id)
                ->where('storage_object_id', $storageObjectId)
                ->first();

            $maxDiscount = $product->categories->max('default_discount');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'base_price' => $product->base_price,
                'discounted_price' => round($product->base_price - ($product->base_price * $maxDiscount / 100), 2),
                'discount_percent' => $maxDiscount,
                'available_quantity' => $stock ? $stock->quantity : 0,
                'unit' => $product->unitOfMeasure?->symbol ?? 'бр.',
            ];
        });

        return response()->json($products->values());
    }


    /**
     * Създаване на разписка за Restaurant POS
     */
    public function restaurantReceipt(Request $request)
    {
        try {
            DB::beginTransaction();

            // Вземаме складовия обект на потребителя
            $storageObject = $this->getCurrentStorageObject();

            if (!$storageObject) {
                return response()->json(['success' => false, 'message' => 'Нямате асоцииран складов обект. Моля, свържете се с администратор.'], 400);
            }

            $data = $request->validate([
                'cart_id' => 'required|exists:active_carts,id',
                'client_id' => 'nullable|exists:clients,id',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.price' => 'required|numeric|min:0',
                'table_number' => 'nullable|integer',
                'payment_method' => 'nullable|string',
                'amount_paid' => 'nullable|numeric|min:0',
                'change_amount' => 'nullable|numeric|min:0',
            ]);

            // Използваме складовия обект на потребителя
            $data['storage_object_id'] = $storageObject->id;

            $cart = ActiveCart::where('user_id', Auth::id())
                ->where('id', $data['cart_id'])
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json(['success' => false, 'message' => 'Количката не беше намерена'], 404);
            }

            $receiptNumber = 'R-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $totalVat = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $price = floatval($item['price']);
                $quantity = floatval($item['quantity']);
                $vatRate = floatval($product->vat_rate);
                $vatAmount = ($price * $vatRate / 100) * $quantity;

                $totalAmount += $price * $quantity;
                $totalVat += $vatAmount;

                $itemsData[] = [
                    'product' => $product,
                    'price' => $price,
                    'quantity' => $quantity,
                    'vatRate' => $vatRate,
                ];
            }

            $receipt = Receipt::create([
                'owner_id' => $cart->owner_id,
                'storage_object_id' => $data['storage_object_id'],
                'client_id' => $data['client_id'] ?? null,
                'user_id' => Auth::id(),
                'receipt_number' => $receiptNumber,
                'type' => 'sale',
                'total_amount' => $totalAmount,
                'total_vat' => $totalVat,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'amount_paid' => $data['amount_paid'] ?? ($totalAmount + $totalVat),
                'change_amount' => $data['change_amount'] ?? 0,
                'notes' => $data['table_number'] ? "Маса: {$data['table_number']}" : null,
                'is_invoiced' => false,
            ]);

            foreach ($itemsData as $itemData) {
                // Намери или създай вариант
                $variant = ProductVariant::firstOrCreate(
                    ['product_id' => $itemData['product']->id],
                    ['color_id' => null, 'size_id' => null, 'sku_suffix' => null, 'price_adjustment' => 0, 'is_active' => true]
                );

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $itemData['product']->name,
                    'sku_snapshot' => $itemData['product']->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'vat_rate' => $itemData['vatRate'],
                    'total' => $itemData['price'] * $itemData['quantity'],
                    'unit_of_measure_snapshot' => $itemData['product']->unitOfMeasure?->symbol ?? 'бр.',
                    'decimal_places_snapshot' => $itemData['product']->unitOfMeasure?->decimal_places ?? 0,
                ]);

                // Намаляване на наличността
                $stock = Stock::where('product_variant_id', $variant->id)
                    ->where('storage_object_id', $data['storage_object_id'])
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $itemData['quantity']);
                }
            }

            // Маркираме количката като завършена
            $cart->status = 'completed';
            $cart->save();

            // Създаваме нова количка за следваща поръчка
            $newCart = $this->cartService->createCart($data['storage_object_id']);

            DB::commit();

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
                'receipt_number' => $receiptNumber,
                'new_cart_id' => $newCart->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Restaurant receipt error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Взема текущия собственик на логнатия потребител
     */
    private function getCurrentOwnerId(): int
    {
        $user = Auth::user();

        if (!$user) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }

        if ($user->hasRole('super_admin')) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }

        if ($user->owner_id) {
            return $user->owner_id;
        }

        if (session()->has('current_owner_id')) {
            return session()->get('current_owner_id');
        }

        $owner = Owner::first();
        return $owner?->id ?? 1;
    }

    public function allProducts()
    {
        $user = Auth::user();

        // Вземаме складовия обект на потребителя
        $storageObject = $this->getCurrentStorageObject();

        if (!$storageObject) {
            return response()->json([]);
        }

        $storageObjectId = $storageObject->id;

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $user->owner_id ?? $this->getCurrentOwnerId())
            ->get()
            ->filter(function ($product) use ($storageObjectId) {
                $variant = $product->variants()->first();
                if (!$variant)
                    return false;

                $stock = Stock::where('product_variant_id', $variant->id)
                    ->where('storage_object_id', $storageObjectId)
                    ->first();

                return $stock && $stock->quantity > 0;
            })
            ->map(function ($product) use ($storageObjectId) {
                $variant = $product->variants()->first();
                $stock = Stock::where('product_variant_id', $variant->id)
                    ->where('storage_object_id', $storageObjectId)
                    ->first();

                $maxDiscount = $product->categories->max('default_discount');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'base_price' => $product->base_price,
                    'discounted_price' => round($product->base_price - ($product->base_price * $maxDiscount / 100), 2),
                    'discount_percent' => $maxDiscount,
                    'available_quantity' => $stock ? $stock->quantity : 0,
                    'unit' => $product->unitOfMeasure?->symbol ?? 'бр.',
                ];
            });

        return response()->json($products->values());
    }

    /**
     * Взема текущия складов обект на потребителя
     */
    /**
     * Взема текущия складов обект на потребителя
     */
    private function getCurrentStorageObject()
    {
        $user = Auth::user();

        // Ако потребителят има директен складов обект
        if ($user->storage_object_id) {
            $storageObject = StorageObject::find($user->storage_object_id);
            if ($storageObject && $storageObject->is_active) {
                return $storageObject;
            }
        }

        // За super_admin, взимаме първия активен склад на текущия собственик
        if ($user->hasRole('super_admin')) {
            $ownerId = $this->getCurrentOwnerId();
            $storageObject = StorageObject::where('owner_id', $ownerId)
                ->where('is_active', true)
                ->first();
            if ($storageObject) {
                return $storageObject;
            }
        }

        return null;
    }

    /**
     * Взема ID на текущия складов обект
     */
    private function getCurrentStorageObjectId(): int
    {
        $storageObject = $this->getCurrentStorageObject();
        if (!$storageObject) {
            abort(403, 'Нямате асоцииран складов обект. Моля, свържете се с администратор.');
        }
        return $storageObject->id;
    }
}