<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                TextInput::make('name')
                    ->label('Име')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('sku')
                    ->label('Артикул (SKU)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                    
                Select::make('type')
                    ->label('Тип')
                    ->options([
                        'product' => '📦 Продукт (стока)',
                        'service' => '⚙️ Услуга',
                    ])
                    ->required()
                    ->default('product')
                    ->live()
                    ->afterStateUpdated(fn ($state, $set) => 
                        $set('has_variants', $state === 'product')
                    ),
                    
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->columnSpanFull(),
                    
                TextInput::make('base_price')
                    ->label('Базова цена')
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
                    
                TextInput::make('cost')
                    ->label('Себестойност')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),
                    
                TextInput::make('vat_rate')
                    ->label('ДДС (%)')
                    ->numeric()
                    ->default(20)
                    ->required(),
                    
                Toggle::make('has_variants')
                    ->label('Има варианти (цветове/размери)')
                    ->default(false)
                    ->visible(fn ($get) => $get('type') === 'product')
                    ->helperText('Активирай ако продуктът се предлага в различни цветове и/или размери'),
                    
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
