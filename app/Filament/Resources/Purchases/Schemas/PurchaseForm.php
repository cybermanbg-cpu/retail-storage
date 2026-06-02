<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Данни')
                    ->schema([
                        Section::make('Основни данни')
                            ->schema([
                                Hidden::make('user_id')->default(fn() => Auth::id()),
                                Hidden::make('owner_id')->default(fn() => Auth::user()->owner_id ?? 1),

                                Select::make('storage_object_id')
                                    ->label('Обект/Склад')
                                    ->options(function () {
                                        $user = Auth::user();
                                        $query = \App\Models\StorageObject::query();

                                        if (!$user->hasRole('super_admin') && $user->owner_id) {
                                            $query->where('owner_id', $user->owner_id);
                                        } elseif (!$user->hasRole('super_admin')) {
                                            return [];
                                        }

                                        return $query->get()
                                            ->mapWithKeys(fn($item) => [$item->id => $item->name . ' (' . $item->owner->name . ')']);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('supplier_id')
                                    ->label('Доставчик')
                                    ->relationship('supplier', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('purchase_number')
                                    ->label('Номер')
                                    ->maxLength(100)
                                    ->required(),

                                DatePicker::make('purchase_date')
                                    ->label('Дата на покупка')
                                    ->required()
                                    ->default(now()),

                                DatePicker::make('invoice_date')
                                    ->label('Дата на фактура')
                                    ->nullable(),

                                TextInput::make('supplier_invoice')
                                    ->label('Номер на фактура')
                                    ->maxLength(100)
                                    ->nullable(),
                            ])->columnSpan(1),

                        Section::make('Финансова информация')
                            ->schema([
                                TextInput::make('discount')
                                    ->label('Отстъпка (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        self::recalculateTotals($set, $get);
                                    }),

                                TextInput::make('delivery_cost')
                                    ->label('Транспорт (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        self::recalculateTotals($set, $get);
                                    }),

                                TextInput::make('vat')
                                    ->label('ДДС (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->default(20)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        self::recalculateTotals($set, $get);
                                    }),

                                // === SUBTOTAL ===
                                TextInput::make('subtotal')
                                    ->label('Междинна сума')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)           // ← важно!
                                    ->extraAttributes(['class' => 'bg-gray-100']),

                                Hidden::make('subtotal'),         // ← това ще се изпраща към сървъра

                                // === TOTAL ===
                                TextInput::make('total')
                                    ->label('Обща сума')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)           // ← важно!
                                    ->extraAttributes(['class' => 'font-bold text-lg']),

                                Hidden::make('total'),            // ← това ще се изпраща към сървъра
                            ])
                            ->columnSpan(1),
                    ])->columns(2)->columnSpan(2)->collapsible(),

                Repeater::make('items')
                    ->label('Артикули')
                    ->relationship('items')
                    ->schema([
                        Grid::make(6)->schema([
                            Hidden::make('product_id'),
                            Hidden::make('real_product_id'),
                            Hidden::make('delivery_cost_share'),

                            Select::make('product_variant_id')
                                ->label('Продукт')
                                ->options(function () {
                                    $options = [];

                                    foreach (ProductVariant::with(['product', 'color', 'size'])
                                        ->whereHas('product', fn($q) => $q->where('type', 'product')->where('is_active', true))
                                        ->get() as $variant) {
                                        $label = $variant->product->name;
                                        if ($variant->color)
                                            $label .= ' - ' . $variant->color->name;
                                        if ($variant->size)
                                            $label .= ' / ' . $variant->size->name;
                                        $options[$variant->id] = $label . ' [' . $variant->product->sku . ']';
                                    }

                                    foreach (Product::where('type', 'product')->where('is_active', true)->whereDoesntHave('variants')->get() as $product) {
                                        $options['product_' . $product->id] = $product->name . ' [' . $product->sku . '] (без вариант)';
                                    }

                                    return $options;
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn($state, $set, $get) => self::handleProductSelection($state, $set, $get))
                                ->columnSpan(2),

                            TextInput::make('quantity')
                                ->label('Количество')
                                ->numeric()
                                ->required()
                                ->minValue(0.001)
                                ->step(0.001)
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function ($set, $get, $state) {
                                    $quantity = floatval(str_replace(',', '.', $state ?? 1));

                                    // Защита от нула или отрицателно
                                    if ($quantity <= 0) {
                                        $quantity = 0.001;
                                        $set('quantity', 0.001); // поправяме визуално
                                    }

                                    self::updateItemTotals($set, $get, $quantity);
                                    self::recalculateTotals($set, $get);
                                })
                                ->columnSpan(1),

                            TextInput::make('unit_cost')
                                ->label('Ед. цена')
                                ->numeric()
                                ->required()
                                ->step(0.01)
                                ->prefix('€')
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($set, $get, $state) {
                                    $unitCost = floatval(str_replace(',', '.', $state ?? 0));

                                    // Защита от отрицателна цена
                                    if ($unitCost < 0) {
                                        $unitCost = 0;
                                        $set('unit_cost', 0);
                                    }

                                    self::updateItemTotals($set, $get, null);
                                    self::recalculateTotals($set, $get);
                                })
                                ->columnSpan(1),

                            TextInput::make('total_cost')
                                ->label('Общо')
                                ->numeric()
                                ->prefix('€')
                                ->disabled()
                                ->dehydrated(false)
                                ->extraAttributes(['class' => 'bg-gray-100']),

                            Hidden::make('total_cost'),

                            TextInput::make('final_unit_cost')
                                ->label('Крайна цена')
                                ->numeric()
                                ->prefix('€')
                                ->disabled()
                                ->dehydrated(false)
                                ->extraAttributes(['class' => 'bg-gray-100']),

                            Hidden::make('final_unit_cost'),
                        ]),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->maxItems(50)
                    ->columnSpanFull()
                    ->live()
                    ->cloneable()
                    ->reorderable()
                    ->addActionLabel('➕ Добави артикул')
                    ->afterStateUpdated(fn($state, $set, $get) => self::recalculateTotals($set, $get)),

                Textarea::make('notes')
                    ->label('Бележки')
                    ->rows(2)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Статус')
                    ->options(['draft' => 'Чернова', 'completed' => 'Завършена', 'cancelled' => 'Анулирана'])
                    ->default('draft')
                    ->required(),
            ]);
    }

    protected static function handleProductSelection($state, $set, $get)
    {
        if (is_string($state) && str_starts_with($state, 'product_')) {
            $set('product_id', str_replace('product_', '', $state));
        } else {
            $variant = ProductVariant::with('product')->find($state);
            if ($variant)
                $set('product_id', $variant->product_id);
        }
    }

    protected static function updateItemTotals($set, $get, $quantity = null)
    {
        $qty = $quantity ?? floatval(str_replace(',', '.', $get('quantity') ?? 1));
        $unitCost = floatval(str_replace(',', '.', $get('unit_cost') ?? 0));

        // Допълнителна защита
        if ($qty <= 0)
            $qty = 0.001;
        if ($unitCost < 0)
            $unitCost = 0;

        $total = $qty * $unitCost;

        $set('total_cost', round($total, 2));
        $set('final_unit_cost', round($unitCost, 4));
    }

    protected static function recalculateTotals($set, $get)
    {
        $items = $get('items') ?? [];

        if (empty($items)) {
            $set('subtotal', 0);
            $set('total', 0);
            return;
        }

        $subtotal = 0;

        foreach ($items as $index => $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $unitCost = floatval($item['unit_cost'] ?? 0);
            $totalCost = $quantity * $unitCost;

            $subtotal += $totalCost;

            $set("items.{$index}.total_cost", round($totalCost, 2));
            $set("items.{$index}.final_unit_cost", round($unitCost, 4));
        }

        $deliveryCost = floatval($get('delivery_cost') ?? 0);
        $discountPercent = floatval($get('discount') ?? 0);
        $vatPercent = floatval($get('vat') ?? 0);

        $discountAmount = $subtotal * ($discountPercent / 100);
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        $totalBeforeVat = $subtotalAfterDiscount + $deliveryCost;

        // Разпределяне на транспорта
        if ($subtotal > 0 && $deliveryCost > 0) {
            foreach ($items as $index => $item) {
                $quantity = floatval($item['quantity'] ?? 1);
                $unitCost = floatval($item['unit_cost'] ?? 0);
                $totalCost = $quantity * $unitCost;

                // Защита от деление на нула
                $deliveryShare = ($subtotal > 0)
                    ? ($totalCost / $subtotal) * $deliveryCost
                    : 0;

                $finalUnitCost = $unitCost + ($quantity > 0 ? ($deliveryShare / $quantity) : 0);

                $set("items.{$index}.delivery_cost_share", round($deliveryShare, 4));
                $set("items.{$index}.final_unit_cost", round($finalUnitCost, 4));
            }
        }

        $vatAmount = $totalBeforeVat * ($vatPercent / 100);
        $total = $totalBeforeVat + $vatAmount;

        // Важно: обновяваме и двете полета
        $set('subtotal', round($subtotal, 2));
        $set('total', round($total, 2));
    }
}