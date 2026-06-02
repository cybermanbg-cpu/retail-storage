<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');
        $isOwner = $user && $user->hasRole('owner');
        $isManager = $user && $user->hasRole('manager');
        
        // Само admin, owner и manager могат да редактират
        $canEdit = $isSuperAdmin || $isOwner || $isManager;

        return $schema
            ->components([
                Section::make('Данни за фактурата')
                    ->schema([
                        Section::make('Основни данни')
                            ->schema([
                                Hidden::make('owner_id')->default(fn() => Auth::user()->owner_id ?? 1),

                                // Собственик – само за super_admin
                                Select::make('owner_id')
                                    ->label('Собственик')
                                    ->relationship('owner', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->visible($isSuperAdmin),

                                Select::make('client_id')
                                    ->label('Клиент')
                                    ->relationship('client', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(!$canEdit)
                                    ->dehydrated(true),

                                TextInput::make('invoice_number')
                                    ->label('Номер на фактура')
                                    ->required()
                                    ->maxLength(50)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'bg-gray-100']),

                                Hidden::make('invoice_number'),

                                DatePicker::make('issue_date')
                                    ->label('Дата на издаване')
                                    ->required()
                                    ->default(now())
                                    ->disabled(!$canEdit)
                                    ->dehydrated(true),

                                DatePicker::make('due_date')
                                    ->label('Падеж')
                                    ->nullable()
                                    ->disabled(!$canEdit)
                                    ->dehydrated(true),

                                Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        'draft' => 'Чернова',
                                        'issued' => 'Издадена',
                                        'paid' => 'Платена',
                                        'cancelled' => 'Анулирана',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->disabled(!$canEdit)
                                    ->dehydrated(true),

                                Textarea::make('notes')
                                    ->label('Бележки')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->disabled(!$canEdit)
                                    ->dehydrated(true),
                            ])
                            ->columnSpan(1),

                        Section::make('Финансова информация')
                            ->schema([
                                // === SUBTOTAL ===
                                TextInput::make('subtotal')
                                    ->label('Междинна сума')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'bg-gray-100']),

                                Hidden::make('subtotal'),

                                TextInput::make('discount')
                                    ->label('Отстъпка')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->default(0)
                                    ->live()
                                    ->disabled(!$canEdit)
                                    ->afterStateUpdated(fn($set, $get) => self::recalculateTotal($set, $get)),

                                // === VAT ===
                                TextInput::make('vat')
                                    ->label('ДДС (20%)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'bg-gray-100']),

                                Hidden::make('vat'),

                                // === TOTAL ===
                                TextInput::make('total')
                                    ->label('Обща сума')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'font-bold text-lg bg-gray-100']),

                                Hidden::make('total'),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->collapsible(),
            ]);
    }

    /**
     * Преизчисляване на ДДС и обща сума
     */
    protected static function recalculateTotal($set, $get)
    {
        $subtotal = floatval($get('subtotal') ?? 0);
        $discount = floatval($get('discount') ?? 0);
        $totalAfterDiscount = $subtotal - $discount;
        $vat = round($totalAfterDiscount * 0.20, 2);
        $total = round($totalAfterDiscount + $vat, 2);

        $set('vat', $vat);
        $set('total', $total);
    }
}