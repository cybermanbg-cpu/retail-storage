<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoppingSession;
use App\Models\ShoppingSessionItem;
use App\Models\Stock;
use App\Models\StorageObject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShoppingMallPosController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $cashierEmails = ['cashier@example.com', 'admin@example.com'];
        $isCashier = in_array($user->email, $cashierEmails) || $user->id == 1;

        $ownerId = $this->getCurrentOwnerId();

        // Вземаме собственика
        $owner = Owner::find($ownerId);

        // Вземаме складовия обект от потребителя
        $storageObject = $this->getCurrentStorageObject();

        $activeSessions = ShoppingSession::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->with([
                'items' => function ($q) {
                    $q->with('kiosk')->orderBy('created_at');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $ownerId)
            ->get();

        $kiosks = User::where('owner_id', $ownerId)->get();

        return view('pos.shopping-mall', compact(
            'activeSessions',
            'products',
            'kiosks',
            'storageObject',
            'isCashier',
            'owner'
        ));
    }

    public function getSessions()
    {
        $ownerId = $this->getCurrentOwnerId();

        $sessions = ShoppingSession::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_token' => $session->session_token,
                    'customer_name' => $session->customer_name,
                    'note' => $session->note,
                    'total_amount' => $session->total_amount,
                    'items_count' => $session->items()->count(),
                    'created_at' => $session->created_at->diffForHumans(),
                ];
            });

        return response()->json($sessions);
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'note' => 'nullable|string'
        ]);

        $ownerId = $this->getCurrentOwnerId();

        $session = ShoppingSession::create([
            'session_token' => strtoupper(Str::random(8)),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'note' => $request->note,
            'owner_id' => $ownerId,
            'created_by' => Auth::id(),
            'status' => 'active',
            'total_amount' => 0,
        ]);

        return response()->json([
            'success' => true,
            'session' => $session
        ]);
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'session_token' => 'required|exists:shopping_sessions,session_token',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0'
        ]);

        // Проверка на наличност
        $storageObjectId = $this->getCurrentStorageObjectId();
        $stockCheck = $this->checkStock($request->product_id, $storageObjectId, $request->quantity);

        if (!$stockCheck['available']) {
            return response()->json([
                'success' => false,
                'message' => $stockCheck['message'],
                'available_quantity' => $stockCheck['quantity']
            ], 400);
        }

        $session = ShoppingSession::where('session_token', $request->session_token)
            ->where('status', 'active')
            ->firstOrFail();

        $product = Product::findOrFail($request->product_id);

        DB::beginTransaction();
        try {
            $item = ShoppingSessionItem::create([
                'shopping_session_id' => $session->id,
                'kiosk_id' => Auth::id(),
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->quantity * $request->unit_price,
                'unit' => $product->unit_symbol,
            ]);

            // Обновяване на общата сума
            $session->total_amount = $session->items()->sum('total_price');
            $session->save();

            DB::commit();

            $session->load(['items.kiosk']);

            return response()->json([
                'success' => true,
                'item' => $item,
                'session' => $session
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeItem($itemId)
    {
        $item = ShoppingSessionItem::findOrFail($itemId);
        $session = $item->shoppingSession;

        if ($session->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Сесията не е активна'], 400);
        }

        // Само създателят на артикула или касиер/админ може да го премахне
        $user = Auth::user();
        if ($item->kiosk_id !== $user->id && !$user->hasRole(['cashier', 'admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Нямате права да премахнете този артикул'], 403);
        }

        DB::beginTransaction();
        try {
            $item->delete();

            $session->total_amount = $session->items()->sum('total_price');
            $session->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'session' => $session->load(['items.kiosk'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateItemQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.001'
        ]);

        $item = ShoppingSessionItem::findOrFail($itemId);
        $session = $item->shoppingSession;

        if ($session->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Сесията не е активна'], 400);
        }

        $user = Auth::user();
        if ($item->kiosk_id !== $user->id && !$user->hasRole(['cashier', 'admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Нямате права да редактирате този артикул'], 403);
        }

        DB::beginTransaction();
        try {
            $item->quantity = $request->quantity;
            $item->total_price = $item->quantity * $item->unit_price;
            $item->save();

            $session->total_amount = $session->items()->sum('total_price');
            $session->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'session' => $session->load(['items.kiosk'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSessionNote(Request $request, $sessionId)
    {
        $request->validate([
            'note' => 'nullable|string'
        ]);

        $session = ShoppingSession::findOrFail($sessionId);
        $session->note = $request->note;
        $session->save();

        return response()->json(['success' => true, 'session' => $session]);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'session_token' => 'required|exists:shopping_sessions,session_token',
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'amount_paid' => 'required|numeric|min:0'
        ]);

        $session = ShoppingSession::where('session_token', $request->session_token)
            ->where('status', 'active')
            ->firstOrFail();

        if ($request->amount_paid < $session->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Въведената сума е по-малка от дължимата'
            ], 400);
        }

        // Вземаме storage_object_id от потребителя
        $storageObjectId = $this->getCurrentStorageObjectId();

        DB::beginTransaction();
        try {
            // Намаляване на наличността за всеки продукт
            foreach ($session->items as $item) {
                $variant = ProductVariant::where('product_id', $item->product_id)->first();
                if ($variant) {
                    $stock = Stock::where('product_variant_id', $variant->id)
                        ->where('storage_object_id', $storageObjectId)
                        ->first();

                    if ($stock) {
                        $stock->decrement('quantity', $item->quantity);
                    }
                }
            }

            $session->status = 'completed';
            $session->payment_method = $request->payment_method;
            $session->paid_amount = $request->amount_paid;
            $session->change_amount = $request->amount_paid - $session->total_amount;
            $session->paid_by = Auth::id();
            $session->paid_at = now();
            $session->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'session' => $session,
                'receipt_number' => 'SM-' . $session->id . '-' . date('Ymd')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelSession($sessionId)
    {
        $session = ShoppingSession::findOrFail($sessionId);

        if ($session->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Сесията не може да бъде анулирана'], 400);
        }

        $session->status = 'cancelled';
        $session->save();

        return response()->json(['success' => true]);
    }

    private function checkStock($productId, $storageObjectId, $quantity)
    {
        // Вземаме първия вариант на продукта
        $variant = ProductVariant::where('product_id', $productId)->first();

        if (!$variant) {
            return ['available' => false, 'message' => 'Продуктът няма вариант', 'quantity' => 0];
        }

        // Проверка в Stock модела
        $stock = Stock::where('product_variant_id', $variant->id)
            ->where('storage_object_id', $storageObjectId)
            ->first();

        if (!$stock) {
            return ['available' => false, 'message' => 'Продуктът не е намерен в склада', 'quantity' => 0];
        }

        $availableQuantity = $stock->available;

        if ($availableQuantity < $quantity) {
            return [
                'available' => false,
                'message' => "Няма достатъчна наличност! Налично: {$availableQuantity} " . ($variant->product->unit_symbol ?? 'бр.'),
                'quantity' => $availableQuantity
            ];
        }

        return ['available' => true, 'message' => '', 'quantity' => $availableQuantity];
    }

    public function getSession($token)
    {
        $session = ShoppingSession::where('session_token', $token)
            ->with([
                'items' => function ($q) {
                    $q->with('kiosk')->orderBy('created_at');
                }
            ])
            ->firstOrFail();

        return response()->json($session);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        $ownerId = $this->getCurrentOwnerId();
        $storageObjectId = $this->getCurrentStorageObjectId();

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $ownerId)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($product) use ($storageObjectId) {
                // Вземаме наличността
                $variant = $product->variants()->first();
                $availableQty = 0;

                if ($variant) {
                    $stock = Stock::where('product_variant_id', $variant->id)
                        ->where('storage_object_id', $storageObjectId)
                        ->first();
                    $availableQty = $stock ? $stock->available : 0;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->base_price,
                    'sku' => $product->sku,
                    'unit' => $product->unit_symbol,
                    'available_qty' => $availableQty,
                ];
            });

        return response()->json($products);
    }

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

    /**
     * Взема текущия складов обект от потребителя
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

        // Ако няма, показваме грешка
        abort(403, 'Нямате асоцииран складов обект. Моля, свържете се с администратор.');
    }

    /**
     * Взема ID на текущия складов обект
     */
    private function getCurrentStorageObjectId(): int
    {
        return $this->getCurrentStorageObject()->id;
    }

    public function getSessionSummary($token)
    {
        $session = ShoppingSession::where('session_token', $token)
            ->with([
                'items' => function ($q) {
                    $q->with('kiosk')->orderBy('created_at');
                }
            ])
            ->firstOrFail();

        // Групиране на артикулите по щандове
        $itemsByKiosk = [];
        foreach ($session->items as $item) {
            $kioskName = $item->kiosk->name;
            if (!isset($itemsByKiosk[$kioskName])) {
                $itemsByKiosk[$kioskName] = [
                    'kiosk_id' => $item->kiosk_id,
                    'kiosk_name' => $kioskName,
                    'items' => [],
                    'subtotal' => 0
                ];
            }
            $itemsByKiosk[$kioskName]['items'][] = $item;
            $itemsByKiosk[$kioskName]['subtotal'] += $item->total_price;
        }

        return response()->json([
            'session' => $session,
            'items_by_kiosk' => $itemsByKiosk,
            'total_kiosks' => count($itemsByKiosk)
        ]);
    }

    public function kioskIndex()
    {
        $ownerId = $this->getCurrentOwnerId();
        $owner = Owner::find($ownerId);
        $storageObject = $this->getCurrentStorageObject();
        $storageObjectId = $storageObject->id;

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $ownerId)
            ->get()
            ->map(function ($product) use ($storageObjectId) {
                $variant = $product->variants()->first();
                $availableQty = 0;

                if ($variant) {
                    $stock = Stock::where('product_variant_id', $variant->id)
                        ->where('storage_object_id', $storageObjectId)
                        ->first();
                    $availableQty = $stock ? $stock->available : 0;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'base_price' => $product->base_price,
                    'unit_symbol' => $product->unit_symbol,
                    'available_qty' => $availableQty
                ];
            });

        // ⭐ ГЕНЕРИРАНЕ НА ИКОНИ ОТ HELPER-А ⭐
        $allProducts = Product::where('type', 'product')
            ->where('is_active', true)
            ->where('owner_id', $ownerId)
            ->get();

        $productIcons = [];
        foreach ($allProducts as $product) {
            $productIcons[$product->name] = getProductIcon($product->name);
        }

        return view('pos.shopping-mall-kiosk', compact('products', 'owner', 'storageObject', 'productIcons'));
    }
}