<?php

namespace App\Filament\Resources\Receipts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');
        $isOwner = $user && $user->hasRole('owner');
        $isManager = $user && $user->hasRole('manager');
        
        // Само admin, owner и manager могат да редактират хедърната част
        $canEditHeader = $isSuperAdmin || $isOwner || $isManager;
        
        return $schema
            ->components([
                Section::make('Данни за продажбата')
                    ->schema([
                        Section::make('Основни данни')
                            ->schema([
                                Hidden::make('user_id')->default(fn() => Auth::id()),
                                Hidden::make('owner_id')->default(fn() => Auth::user()->owner_id ?? 1),

                                // Собственик – само за super_admin
                                Select::make('owner_id')
                                    ->label('Собственик')
                                    ->relationship('owner', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->visible($isSuperAdmin),
                                    
                                // Обект – само за admin/owner/manager
                                Select::make('storage_object_id')
                                    ->label('Обект')
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
                                    ->preload()
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                                    
                                // Клиент
                                Select::make('client_id')
                                    ->label('Клиент')
                                    ->relationship('client', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                                    
                                // Номер на разписка – винаги само за четене
                                TextInput::make('receipt_number')
                                    ->label('Номер на разписка')
                                    ->required()
                                    ->maxLength(50)
                                    ->disabled()
                                    ->dehydrated(true),
                                    
                                // Дата и час – само за admin/owner/manager
                                DateTimePicker::make('created_at')
                                    ->label('Дата и час')
                                    ->required()
                                    ->default(now())
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                                    
                                // Тип – само за admin/owner/manager
                                Select::make('type')
                                    ->label('Тип')
                                    ->options([
                                        'sale' => 'Продажба',
                                        'receipt' => 'Приход',
                                        'write_off' => 'Отписване',
                                        'transfer' => 'Трансфер',
                                        'inventory' => 'Инвентаризация',
                                    ])
                                    ->required()
                                    ->default('sale')
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                            ])
                            ->columnSpan(1),

                        Section::make('Финансова информация')
                            ->schema([
                                // Начин на плащане – само за admin/owner/manager
                                Select::make('payment_method')
                                    ->label('Начин на плащане')
                                    ->options([
                                        'cash' => 'В брой',
                                        'card' => 'Карта',
                                        'bank_transfer' => 'Банков превод',
                                    ])
                                    ->nullable()
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                                    
                                // === SUBTOTAL ===
                                TextInput::make('total_amount')
                                    ->label('Обща сума')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'bg-gray-100']),
                                    
                                Hidden::make('total_amount'),
                                    
                                // === ДДС ===
                                TextInput::make('total_vat')
                                    ->label('ДДС (20%)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraAttributes(['class' => 'bg-gray-100']),
                                    
                                Hidden::make('total_vat'),
                                    
                                // === TOTAL ===
                                // TextInput::make('grand_total')
                                //     ->label('Обща сума (с ДДС)')
                                //     ->numeric()
                                //     ->prefix('€')
                                //     ->disabled()
                                //     ->dehydrated(false)
                                //     ->extraAttributes(['class' => 'font-bold text-lg bg-gray-100']),
                                    
                                Hidden::make('grand_total'),

                                // Статус – само за admin/owner/manager
                                // Select::make('status')
                                //     ->label('Статус')
                                //     ->options([
                                //         'active' => 'Активна',
                                //         'completed' => 'Завършена',
                                //         'cancelled' => 'Анулирана',
                                //     ])
                                //     ->default('completed')
                                //     ->required()
                                //     ->disabled(!$canEditHeader)
                                //     ->dehydrated(true),
                                    
                                // Бележки
                                Textarea::make('notes')
                                    ->label('Бележки')
                                    ->rows(1)
                                    ->columnSpanFull()
                                    ->disabled(!$canEditHeader)
                                    ->dehydrated(true),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->collapsible(),
            ]);
    }
}