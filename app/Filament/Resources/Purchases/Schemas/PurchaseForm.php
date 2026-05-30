<?php

namespace App\Filament\Resources\Purchases\Schemas;

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
                // Grid::make(2)
                // ->schema([
                // Лява колона
                Section::make('Основни данни')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                        Hidden::make('owner_id')
                            ->default(fn() => Auth::user()->owner_id ?? 1),

                        Select::make('storage_object_id')
                            ->label('Обект/Склад')
                            ->relationship('storageObject', 'name')
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
                            ->label('Номер Стокова')
                            ->maxLength(100)
                            ->required()
                            ->nullable(),

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

                // Дясна колона
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
                // ]),

                // Артикули
                // Section::make('Артикули')
                //     ->schema([
                Repeater::make('items')
                    ->label('')
                    ->relationship('items')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                Select::make('product_variant_id')
                                    ->label('Продукт / Вариант')
                                    ->relationship('productVariant', 'id')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) =>
                                        $record->product->name .
                                        ($record->color ? ' - ' . $record->color->name : '') .
                                        ($record->size ? ' / ' . $record->size->name : '')
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        self::updateItemTotals($set, $get, null);
                                    }),

                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($set, $get, $state) =>
                                        self::updateItemTotals($set, $get, $state)
                                    ),

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
                // ]),

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
     * Изчислява общата сума за един артикул
     */
    protected static function updateItemTotals($set, $get, $quantity = null)
    {
        $qty = $quantity ?? $get('quantity') ?? 1;
        $unitCost = $get('unit_cost') ?? 0;
        $total = $qty * $unitCost;

        $set('total_cost', round($total, 2));

        // Първоначално final_unit_cost е равен на unit_cost
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
            $subtotal += ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0);
        }

        $deliveryCost = $get('delivery_cost') ?? 0;
        $discount = $get('discount') ?? 0;
        $vat = $get('vat') ?? 0;

        // Разпределяне на транспортните разходи върху артикулите
        if ($subtotal > 0 && $deliveryCost > 0) {
            foreach ($items as $index => &$item) {
                $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0);
                $share = ($itemTotal / $subtotal) * $deliveryCost;
                $finalUnitCost = ($item['unit_cost'] ?? 0) + ($share / ($item['quantity'] ?? 1));
                $item['final_unit_cost'] = round($finalUnitCost, 2);
            }
        }

        $totalBeforeVat = $subtotal + $deliveryCost - $discount;
        $total = $totalBeforeVat + $vat;

        $set('subtotal', round($subtotal, 2));
        $set('total', round($total, 2));
    }
}