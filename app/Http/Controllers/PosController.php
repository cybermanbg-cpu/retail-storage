<?php

namespace App\Http\Controllers;

use App\Helpers\CartHelper;
use App\Http\Controllers\Controller;
use App\Models\ActiveCart;
use App\Models\Client;
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
        // Вземаме всички активни продукти (с и без варианти)
        $products = Product::with(['variants.color', 'variants.size', 'unitOfMeasure'])
            ->where('type', 'product')
            ->where('is_active', true)
            ->get();

        $clients = Client::where('is_active', true)->get();
        $storageObject = StorageObject::first();

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

            $data = $request->validate([
                'cart_id' => 'required|exists:active_carts,id',
                'storage_object_id' => 'required|exists:storage_objects,id',
                'client_id' => 'nullable|exists:clients,id',
                'payment_method' => 'required|in:cash,card,bank_transfer',
                'amount_paid' => 'required|numeric|min:0',
                'change_amount' => 'nullable|numeric|min:0',
                'items' => 'required|array',
                'items.*.variant_id' => 'required|exists:product_variants,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

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
}