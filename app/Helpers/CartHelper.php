<?php

namespace App\Helpers;

use App\Models\ActiveCart;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;

class CartHelper
{
    /**
     * Генерира уникално име за количка
     */
    public static function generateCartName(): string
    {
        return 'Количка ' . now()->format('H:i') . ' - ' . rand(100, 999);
    }

    /**
     * Проверка на наличност за вариант в даден обект
     */
    public static function checkStock(int $variantId, int $storageObjectId, int $requestedQty = 1): array
    {
        $stock = Stock::where('product_variant_id', $variantId)
            ->where('storage_object_id', $storageObjectId)
            ->first();

        $available = $stock ? $stock->available : 0;

        return [
            'available' => $available,
            'has_stock' => $available >= $requestedQty,
            'current_qty' => $stock ? $stock->quantity : 0,
            'message' => $available >= $requestedQty
                ? 'Наличността е достатъчна'
                : 'Няма достатъчна наличност. Налични: ' . $available,
        ];
    }

    /**
     * Изчислява обща сума на количката
     */
    public static function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += floatval($item['total'] ?? 0);
        }
        return floatval($total);
    }

    /**
     * Изчислява ДДС (20%)
     */
    public static function calculateVat(float $total): float
    {
        return $total * 0.20;
    }

    /**
     * Генерира номер на разписка
     */
    public static function generateReceiptNumber(): string
    {
        return 'R-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}