<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\UnitOfMeasure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');
        
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdmin),
                    
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
                    
                // ⭐ МЕРНА ЕДИНИЦА ⭐
                Select::make('unit_of_measure_id')
                    ->label('Мерна единица')
                    ->options(function () use ($user) {
                        $query = UnitOfMeasure::where('is_active', true);
                        
                        // Ако не е супер администратор, показва глобалните + своите
                        if (!$user->hasRole('super_admin') && $user->owner_id) {
                            $query->where(function ($q) use ($user) {
                                $q->whereNull('owner_id')
                                  ->orWhere('owner_id', $user->owner_id);
                            });
                        }
                        
                        return $query->orderBy('name')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Изберете мерна единица (брой, кг, л, м и т.н.)')
                    ->default(function () {
                        // По подразбиране взима "Брой" (pcs)
                        $defaultUnit = UnitOfMeasure::where('code', 'pcs')->first();
                        return $defaultUnit?->id;
                    }),
                    
                TextInput::make('base_price')
                    ->label('Базова цена')
                    ->numeric()
                    ->required()
                    ->prefix('лв.')
                    ->step(0.01),
                    
                TextInput::make('cost')
                    ->label('Себестойност')
                    ->numeric()
                    ->prefix('лв.')
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