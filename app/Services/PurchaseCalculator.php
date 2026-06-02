<?php

namespace App\Services;

class PurchaseCalculator
{
    /**
     * Преизчислява общите суми на покупката с разпределение на транспортните разходи
     */
    public static function recalculateTotals($set, $get)
    {
        $items = $get('items') ?? [];
        
        if (empty($items)) {
            $set('subtotal', 0);
            $set('total', 0);
            return;
        }
        
        // 1. Изчисляваме subtotal
        $subtotal = 0;
        $itemsData = [];
        
        foreach ($items as $index => $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $unitCost = floatval($item['unit_cost'] ?? 0);
            $totalCost = $quantity * $unitCost;
            $subtotal += $totalCost;
            
            $itemsData[$index] = [
                'quantity' => $quantity,
                'unitCost' => $unitCost,
                'totalCost' => $totalCost
            ];
            
            $set("items.{$index}.total_cost", round($totalCost, 2));
        }
        
        // 2. Вземаме стойностите
        $deliveryCost = floatval($get('delivery_cost') ?? 0);
        $discountPercent = floatval($get('discount') ?? 0);
        $vatPercent = floatval($get('vat') ?? 0);
        
        // 3. Изчисляваме отстъпката
        $discountAmount = $subtotal * ($discountPercent / 100);
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        
        // 4. Добавяме транспортните разходи
        $totalBeforeVat = $subtotalAfterDiscount + $deliveryCost;
        
        // 5. Разпределяме транспортните разходи (само ако има артикули и subtotal > 0)
        if ($subtotal > 0) {
            foreach ($itemsData as $index => $data) {
                $quantity = $data['quantity'];
                $unitCost = $data['unitCost'];
                $totalCost = $data['totalCost'];
                
                // Пропорционален дял
                $weight = $totalCost / $subtotal;
                
                // Транспорт
                $deliveryShare = $weight * $deliveryCost;
                
                // Отстъпка
                $discountShare = $weight * $discountAmount;
                
                // Изчисляваме финалната цена (проверка за деление на нула)
                if ($quantity > 0) {
                    $deliveryPerUnit = $deliveryShare / $quantity;
                    $discountPerUnit = $discountShare / $quantity;
                    $finalUnitCost = $unitCost + $deliveryPerUnit - $discountPerUnit;
                } else {
                    // Ако количеството е 0, не можем да делим
                    $finalUnitCost = $unitCost;
                }
                
                $set("items.{$index}.delivery_cost_share", round($deliveryShare, 4));
                $set("items.{$index}.final_unit_cost", round($finalUnitCost, 4));
            }
        } else {
            // Няма артикули със стойност
            foreach ($items as $index => $item) {
                $unitCost = floatval($item['unit_cost'] ?? 0);
                $set("items.{$index}.final_unit_cost", round($unitCost, 4));
                $set("items.{$index}.delivery_cost_share", 0);
            }
        }
        
        // 6. Изчисляваме ДДС
        $vatAmount = $totalBeforeVat * ($vatPercent / 100);
        $total = $totalBeforeVat + $vatAmount;
        
        // 7. Запазваме изчисленията
        $set('subtotal', round($subtotal, 2));
        $set('total', round($total, 2));
    }
    
    /**
     * Изчислява общата сума за един артикул
     */
    public static function updateItemTotals($set, $get, $quantity = null)
    {
        $qty = floatval($quantity ?? $get('quantity') ?? 0);
        $unitCost = floatval($get('unit_cost') ?? 0);
        $total = $qty * $unitCost;

        $set('total_cost', round($total, 2));
        $set('final_unit_cost', round($unitCost, 2));
        $set('delivery_cost_share', 0);
    }
}