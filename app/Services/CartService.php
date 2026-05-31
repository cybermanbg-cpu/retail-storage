<?php

namespace App\Services;

use App\Helpers\CartHelper;
use App\Models\ActiveCart;
use App\Models\Owner;
use App\Models\ProductVariant;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Взема текущия собственик на потребителя
     */
    protected function getCurrentOwnerId(): int
    {
        $user = Auth::user();

        // За супер администратор или ако няма owner_id
        if (!$user || $user->hasRole('super_admin')) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }

        // Ако потребителят има owner_id
        if ($user->owner_id) {
            return $user->owner_id;
        }

        // Fallback
        $owner = Owner::first();
        return $owner?->id ?? 1;
    }

    /**
     * Проверка дали потребителят може да вижда всички колички
     */
    protected function canViewAllCarts(): bool
    {
        $user = Auth::user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('owner') ||
            $user->hasPermissionTo('view_all_carts')
        );
    }

    /**
     * Създава нова количка
     */
    public function createCart(int $storageObjectId, ?int $clientId = null): ActiveCart
    {
        return ActiveCart::create([
            'user_id' => Auth::id(),
            'owner_id' => $this->getCurrentOwnerId(),
            'cashier_id' => Auth::id(),
            'session_id' => session()->getId(),
            'cart_name' => CartHelper::generateCartName(),
            'client_id' => $clientId,
            'storage_object_id' => $storageObjectId,
            'items' => [],
            'total_amount' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Взема активните колички за текущия касиер/собственик
     */
    public function getActiveCarts(): \Illuminate\Database\Eloquent\Collection
    {
        $query = ActiveCart::forOwner($this->getCurrentOwnerId())
            ->active()
            ->orderBy('updated_at', 'desc');

        // Ако не може да вижда всички колички, показвай само неговите
        if (!$this->canViewAllCarts()) {
            $query->where('cashier_id', Auth::id());
        }

        return $query->get();
    }

    /**
     * Взема ВСИЧКИ активни колички в даден обект (за управител/админ)
     */
    public function getAllActiveCartsInObject(int $storageObjectId): \Illuminate\Database\Eloquent\Collection
    {
        $query = ActiveCart::forOwner($this->getCurrentOwnerId())
            ->where('storage_object_id', $storageObjectId)
            ->active()
            ->with('cashier')
            ->orderBy('updated_at', 'desc');

        // Ако не може да вижда всички колички, показвай само неговите
        if (!$this->canViewAllCarts()) {
            $query->where('cashier_id', Auth::id());
        }

        return $query->get();
    }

    /**
     * Взема конкретна количка (с проверка за права)
     */
    public function getCart(int $cartId): ActiveCart
    {
        $query = ActiveCart::where('owner_id', $this->getCurrentOwnerId())
            ->where('id', $cartId)
            ->where('status', 'active');

        // Ако не може да вижда всички колички, показвай само неговите
        if (!$this->canViewAllCarts()) {
            $query->where('cashier_id', Auth::id());
        }

        return $query->firstOrFail();
    }

    /**
     * Изтрива количка
     */
    public function deleteCart(int $cartId): bool
    {
        $cart = $this->getCart($cartId);
        return (bool) $cart->delete();
    }

    public function addItem(int $cartId, int $variantId, int $quantity = 1): array
    {
        $cart = $this->getCart($cartId);
        $variant = ProductVariant::with(['product', 'color', 'size'])->findOrFail($variantId);

        $stockCheck = CartHelper::checkStock($variantId, $cart->storage_object_id, $quantity);

        if (!$stockCheck['has_stock']) {
            return [
                'success' => false,
                'message' => $stockCheck['message'],
            ];
        }

        $items = $cart->items ?? [];
        $existingIndex = null;

        foreach ($items as $index => $item) {
            if ($item['variant_id'] == $variantId) {
                $existingIndex = $index;
                break;
            }
        }

        $price = floatval($variant->final_price);

        if ($existingIndex !== null) {
            $items[$existingIndex]['quantity'] = intval($items[$existingIndex]['quantity']) + $quantity;
            $items[$existingIndex]['total'] = floatval($items[$existingIndex]['quantity']) * $price;
        } else {
            $items[] = [
                'variant_id' => $variantId,
                'product_name' => $variant->product->name,
                'variant_name' => trim(($variant->color?->name ?? '') . ' ' . ($variant->size?->name ?? '')),
                'price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
                'color_name' => $variant->color?->name,
                'size_name' => $variant->size?->name,
                'sku' => $variant->full_sku,
            ];
        }

        $cart->items = $items;
        $cart->total_amount = floatval(CartHelper::calculateTotal($items));
        $cart->save();

        return [
            'success' => true,
            'cart' => $cart,
            'message' => 'Продуктът е добавен успешно',
        ];
    }

    /**
     * Премахва продукт от количката
     */
    public function removeItem(int $cartId, int $itemIndex): array
    {
        $cart = $this->getCart($cartId);
        $items = $cart->items ?? [];

        if (isset($items[$itemIndex])) {
            array_splice($items, $itemIndex, 1);
            $cart->items = $items;
            $cart->total_amount = CartHelper::calculateTotal($items);
            $cart->save();

            return ['success' => true, 'message' => 'Продуктът е премахнат'];
        }

        return ['success' => false, 'message' => 'Продуктът не беше намерен'];
    }

    /**
     * Актуализира цялата количка
     */
    public function updateCart(int $cartId, array $data): ActiveCart
    {
        $cart = $this->getCart($cartId);

        if (isset($data['items'])) {
            $cart->items = $data['items'];
            $cart->total_amount = CartHelper::calculateTotal($data['items']);
        }

        if (isset($data['client_id'])) {
            $cart->client_id = $data['client_id'];
        }

        if (isset($data['cart_name'])) {
            $cart->cart_name = $data['cart_name'];
        }

        $cart->save();

        return $cart;
    }

    /**
     * Завършва продажбата
     */
    public function completeSale(int $cartId): array
    {
        try {
            DB::beginTransaction();

            $cart = $this->getCart($cartId);

            if (empty($cart->items)) {
                throw new \Exception('Няма продукти в количката');
            }

            // Проверка на наличността за всички продукти
            foreach ($cart->items as $item) {
                $stockCheck = CartHelper::checkStock($item['variant_id'], $cart->storage_object_id, $item['quantity']);
                if (!$stockCheck['has_stock']) {
                    throw new \Exception("Няма достатъчна наличност за: {$item['product_name']}. {$stockCheck['message']}");
                }
            }

            // Създаване на разписка
            $receipt = Receipt::create([
                'owner_id' => $cart->owner_id,
                'storage_object_id' => $cart->storage_object_id,
                'client_id' => $cart->client_id,
                'user_id' => Auth::id(),
                'receipt_number' => CartHelper::generateReceiptNumber(),
                'type' => 'sale',
                'total_amount' => $cart->total_amount,
                'total_vat' => CartHelper::calculateVat($cart->total_amount),
                'notes' => null,
                'is_invoiced' => false,
            ]);

            // Създаване на елементите и намаляване на наличността
            foreach ($cart->items as $item) {
                $variant = ProductVariant::find($item['variant_id']);

                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'product_variant_id' => $item['variant_id'],
                    'product_name_snapshot' => $item['product_name'],
                    'sku_snapshot' => $item['sku'] ?? $variant->full_sku,
                    'color_name' => $item['color_name'] ?? null,
                    'size_name' => $item['size_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'vat_rate' => 20,
                    'total' => $item['total'],
                ]);

                // Намаляване на наличността
                $stock = Stock::where('product_variant_id', $item['variant_id'])
                    ->where('storage_object_id', $cart->storage_object_id)
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $item['quantity']);
                }
            }

            // Маркиране на количката като завършена
            $cart->status = 'completed';
            $cart->save();

            // Създаване на нова количка
            $newCart = $this->createCart($cart->storage_object_id);

            DB::commit();

            return [
                'success' => true,
                'receipt' => $receipt,
                'receipt_number' => $receipt->receipt_number,
                'new_cart' => $newCart,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function addItemWithDetails($cartId, $variantId, $quantity, $unitPrice, $unit, $decimalPlaces)
    {
        $cart = $this->getCart($cartId);

        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant) {
            return ['success' => false, 'message' => 'Продуктът не беше намерен'];
        }

        $items = $cart->items ?? [];
        $existingIndex = null;

        foreach ($items as $index => $item) {
            if ($item['variant_id'] == $variantId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $items[$existingIndex]['quantity'] += $quantity;
            $items[$existingIndex]['total'] = $items[$existingIndex]['quantity'] * $unitPrice;
        } else {
            $items[] = [
                'variant_id' => $variantId,
                'product_name' => $variant->product?->name ?? 'N/A',
                'variant_name' => '',
                'price' => $unitPrice,
                'quantity' => $quantity,
                'total' => $unitPrice * $quantity,
                'unit' => $unit,
                'decimal_places' => $decimalPlaces,
                'sku' => $variant->full_sku ?? '',
            ];
        }

        $cart->items = $items;
        $cart->total_amount = collect($items)->sum('total');
        $cart->save();

        return ['success' => true, 'cart' => $cart];
    }
}