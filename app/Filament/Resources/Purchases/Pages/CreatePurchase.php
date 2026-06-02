<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\ProductVariant;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                // Конвертиране на числата (поддръжка на български формат)
                $item['quantity']   = floatval(str_replace(',', '.', $item['quantity'] ?? 1));
                $item['unit_cost']  = floatval(str_replace(',', '.', $item['unit_cost'] ?? 0));

                // Запазваме изчислените стойности от формата (Hidden + disabled полета)
                $item['total_cost'] = isset($item['total_cost']) 
                    ? floatval(str_replace(',', '.', $item['total_cost'])) 
                    : ($item['quantity'] * $item['unit_cost']);

                $item['final_unit_cost'] = isset($item['final_unit_cost']) 
                    ? floatval(str_replace(',', '.', $item['final_unit_cost'])) 
                    : $item['unit_cost'];

                $item['delivery_cost_share'] = isset($item['delivery_cost_share']) 
                    ? floatval(str_replace(',', '.', $item['delivery_cost_share'])) 
                    : 0;

                // Окръгляне
                $item['quantity']            = round($item['quantity'], 4);
                $item['unit_cost']           = round($item['unit_cost'], 4);
                $item['total_cost']          = round($item['total_cost'], 2);
                $item['final_unit_cost']     = round($item['final_unit_cost'], 4);
                $item['delivery_cost_share'] = round($item['delivery_cost_share'], 4);

                // Обработка на виртуални варианти (product без variant)
                if (isset($item['product_variant_id']) && is_string($item['product_variant_id']) && str_starts_with($item['product_variant_id'], 'product_')) {
                    $productId = str_replace('product_', '', $item['product_variant_id']);
                    $variant = ProductVariant::firstOrCreate(
                        ['product_id' => $productId],
                        [
                            'color_id'       => null,
                            'size_id'        => null,
                            'sku_suffix'     => null,
                            'price_adjustment'=> 0,
                            'is_active'      => true
                        ]
                    );
                    $item['product_variant_id'] = $variant->id;
                    $item['product_id']         = $productId;
                } elseif (isset($item['product_variant_id']) && is_numeric($item['product_variant_id'])) {
                    $variant = ProductVariant::find($item['product_variant_id']);
                    if ($variant) {
                        $item['product_id'] = $variant->product_id;
                    }
                }
            }
        }

        // Основни полета
        $data['discount']      = floatval(str_replace(',', '.', $data['discount'] ?? 0));
        $data['delivery_cost'] = floatval(str_replace(',', '.', $data['delivery_cost'] ?? 0));
        $data['vat']           = floatval(str_replace(',', '.', $data['vat'] ?? 20));

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}