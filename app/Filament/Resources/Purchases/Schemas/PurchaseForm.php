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
                Section::make('Основни данни')
                    ->schema([
                        Hidden::make('user_id')->default(fn() => Auth::id()),
                        Hidden::make('owner_id')->default(fn() => Auth::user()->owner_id ?? 1),

                        Select::make('storage_object_id')
                            ->label('Обект/Склад')
                            ->relationship('storageObject', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' (' . $record->owner->name . ')')
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
                            ->label('Номер на фактура от доставчик')
                            ->maxLength(100)
                            ->nullable(),
                    ])
                    ->columnSpan(1),

                Section::make('Финансова информация')
                    ->schema([
                        TextInput::make('discount')
                            ->label('Отстъпка')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                        TextInput::make('delivery_cost')
                            ->label('Транспортни разходи')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                        TextInput::make('vat')
                            ->label('ДДС (20%)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn($set, $get) => self::recalculateTotals($set, $get)),

                        TextInput::make('subtotal')
                            ->label('Междинна сума')
                            ->numeric()
                            ->prefix('€')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('total')
                            ->label('Обща сума')
                            ->numeric()
                            ->prefix('€')
                            ->disabled()
                            ->dehydrated(true)
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                    ])
                    ->columnSpan(1),

                // ⭐ REPEATER – използва product_variant_id ⭐
                Repeater::make('items')
                    ->label('')
                    ->relationship('items')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                Hidden::make('product_id'),

                                Select::make('product_variant_id')
                                    ->label('Продукт')
                                    ->options(function () {
                                        $options = [];

                                        // Всички варианти на продукти
                                        $variants = ProductVariant::with(['product', 'color', 'size', 'product.unitOfMeasure'])
                                            ->whereHas('product', fn($q) => $q->where('type', 'product')->where('is_active', true))
                                            ->get();

                                        foreach ($variants as $variant) {
                                            $label = $variant->product->name;
                                            if ($variant->color)
                                                $label .= ' - ' . $variant->color->name;
                                            if ($variant->size)
                                                $label .= ' / ' . $variant->size->name;
                                            $unitSymbol = $variant->product->unitOfMeasure?->symbol ?? 'бр.';
                                            $options[$variant->id] = $label . ' [' . $variant->product->sku . '] (' . $unitSymbol . ')';
                                        }

                                        // Продукти без варианти (ако нямат вариант, създай временен)
                                        $productsWithoutVariants = Product::with('unitOfMeasure')
                                            ->where('type', 'product')
                                            ->where('is_active', true)
                                            ->whereDoesntHave('variants')
                                            ->get();

                                        foreach ($productsWithoutVariants as $product) {
                                            // Създаваме временен виртуален вариант
                                            $virtualId = 'product_' . $product->id;
                                            $unitSymbol = $product->unitOfMeasure?->symbol ?? 'бр.';
                                            $options[$virtualId] = $product->name . ' [' . $product->sku . '] (' . $unitSymbol . ') - ⚠️ Няма вариант';
                                        }

                                        return $options;
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        self::handleProductSelection($state, $set, $get);
                                    }),

                                // Скрито поле за продукт без вариант
                                Hidden::make('real_product_id'),

                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->default(1)
                                    ->live()
                                    ->helperText('Допустими са дробни числа (напр. 0.500 кг, 1.75 л)')
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        // Конвертиране от string към float
                                        $quantity = floatval(str_replace(',', '.', $state));
                                        self::updateItemTotals($set, $get, $quantity);
                                    }),

                                TextInput::make('unit_cost')
                                    ->label('Ед. цена')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($set, $get, $state) =>
                                        self::updateItemTotals($set, $get, null)
                                    ),

                                TextInput::make('total_cost')
                                    ->label('Общо')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(true),

                                TextInput::make('final_unit_cost')
                                    ->label('Крайна ед. цена (с транспорт)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(true),
                            ]),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::recalculateTotals($set, $get);
                    }),

                Textarea::make('notes')
                    ->label('Бележки')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Чернова',
                        'completed' => 'Завършена',
                        'cancelled' => 'Анулирана',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }

    /**
     * Обработка на избор на продукт (виртуален вариант)
     */
    protected static function handleProductSelection($state, $set, $get)
    {
        if (is_string($state) && str_starts_with($state, 'product_')) {
            $productId = str_replace('product_', '', $state);
            $set('product_id', $productId);
            $set('real_product_id', $productId);
        } else {
            // Обикновен вариант (product_variant_id)
            $variant = ProductVariant::with('product')->find($state);
            if ($variant) {
                $set('product_id', $variant->product_id);
            }
            $set('real_product_id', null);
        }

        self::updateItemTotals($set, $get, null);
    }

    /**
     * Изчислява общата сума за един артикул
     */
    protected static function updateItemTotals($set, $get, $quantity = null)
    {
        $qty = floatval($quantity ?? $get('quantity') ?? 1);
        $unitCost = floatval($get('unit_cost') ?? 0);
        $total = $qty * $unitCost;

        $set('total_cost', round($total, 2));
        $set('final_unit_cost', round($unitCost, 2));
    }

    /**
     * Преизчислява общите суми на покупката
     */
    protected static function recalculateTotals($set, $get)
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $unitCost = floatval($item['unit_cost'] ?? 0);
            $subtotal += $quantity * $unitCost;
        }

        $deliveryCost = floatval($get('delivery_cost') ?? 0);
        $discount = floatval($get('discount') ?? 0);
        $vat = floatval($get('vat') ?? 0);

        $totalBeforeVat = $subtotal + $deliveryCost - $discount;
        $total = $totalBeforeVat + $vat;

        $set('subtotal', round($subtotal, 2));
        $set('total', round($total, 2));
    }
}