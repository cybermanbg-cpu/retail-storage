<?php

namespace App\Filament\Resources\ShoppingSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShoppingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Информация за сметката')
                    ->schema([
                        TextInput::make('session_token')
                            ->label('Токен')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit'),

                        TextInput::make('customer_name')
                            ->label('Име на клиент')
                            ->maxLength(255),

                        TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(50),

                        Textarea::make('note')
                            ->label('Бележка')
                            ->rows(3)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активна',
                                'payment_pending' => 'Чака плащане',
                                'completed' => 'Завършена',
                                'cancelled' => 'Анулирана',
                            ])
                            ->required()
                            ->default('active')
                            ->native(false),
                    ])->columns(2),

                Section::make('Финансова информация')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Обща сума')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('paid_amount')
                            ->label('Платена сума')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),

                        TextInput::make('change_amount')
                            ->label('Ресто')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),

                        Select::make('payment_method')
                            ->label('Начин на плащане')
                            ->options([
                                'cash' => 'В брой',
                                'card' => 'Карта',
                                'bank_transfer' => 'Банков превод',
                            ])
                            ->native(false),

                        DateTimePicker::make('paid_at')
                            ->label('Платено на'),
                    ])->columns(2),
            ]);
    }
}
