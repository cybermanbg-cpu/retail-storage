<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\ProductVariant;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    /**
     * Обработка на данните преди запис
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Проверка дали има items
        if (!isset($data['items']) || !is_array($data['items'])) {
            return $data;
        }
        
        // Обработка на виртуални варианти (product_XXX)
        foreach ($data['items'] as &$item) {
            if (isset($item['product_variant_id']) && is_string($item['product_variant_id']) && str_starts_with($item['product_variant_id'], 'product_')) {
                $productId = str_replace('product_', '', $item['product_variant_id']);
                
                // Създай или намери вариант
                $variant = ProductVariant::firstOrCreate(
                    ['product_id' => $productId],
                    [
                        'color_id' => null,
                        'size_id' => null,
                        'sku_suffix' => null,
                        'price_adjustment' => 0,
                        'is_active' => true,
                    ]
                );
                
                $item['product_variant_id'] = $variant->id;
                $item['product_id'] = $productId;
            }
        }
        
        return $data;
    }

    /**
     * Действия след успешно създаване
     */
    protected function afterCreate(): void
    {
        $purchase = $this->record;
        
        if (!$purchase) {
            return;
        }
        
        // Зареждане на items с релациите
        $purchase->load('items');
        
        if (!$purchase->items || $purchase->items->isEmpty()) {
            return;
        }
        
        // Актуализиране на наличността
        foreach ($purchase->items as $item) {
            if (!$item->product_variant_id) {
                continue;
            }
            
            $stock = Stock::where('product_variant_id', $item->product_variant_id)
                ->where('storage_object_id', $purchase->storage_object_id)
                ->first();
            
            if ($stock) {
                $stock->quantity += $item->quantity;
                $stock->average_cost = $item->final_unit_cost ?? $item->unit_cost;
                $stock->save();
            } else {
                Stock::create([
                    'product_variant_id' => $item->product_variant_id,
                    'storage_object_id' => $purchase->storage_object_id,
                    'quantity' => $item->quantity,
                    'average_cost' => $item->final_unit_cost ?? $item->unit_cost,
                    'reserved_quantity' => 0,
                    'min_quantity' => 0,
                ]);
            }
        }
    }
}