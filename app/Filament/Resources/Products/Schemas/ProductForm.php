<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Stock;
use App\Models\UnitOfMeasure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
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
                    
                Select::make('unit_of_measure_id')
                    ->label('Мерна единица')
                    ->options(function () use ($user) {
                        $query = UnitOfMeasure::where('is_active', true);
                        
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
                    ->default(fn() => UnitOfMeasure::where('code', 'pcs')->first()?->id),
                    
                TextInput::make('base_price')
                    ->label('Базова цена')
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
                    
                // ⭐ СЕБЕСТОЙНОСТ (СРЕДНА) – чрез afterStateHydrated ⭐
                TextInput::make('average_cost_display')
                    ->label('Себестойност (средна)')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Автоматично изчислена от наличностите')
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if (!$record || $record->type !== 'product') {
                            $component->state(0);
                            return;
                        }
                        
                        $totalQuantity = 0;
                        $totalValue = 0;
                        
                        foreach ($record->variants as $variant) {
                            foreach ($variant->stocks as $stock) {
                                $totalQuantity += $stock->quantity;
                                $totalValue += $stock->quantity * $stock->average_cost;
                            }
                        }
                        
                        $averageCost = $totalQuantity > 0 ? round($totalValue / $totalQuantity, 4) : 0;
                        $component->state($averageCost);
                    }),
                    
                // ⭐ ОБЩА СТОЙНОСТ НА НАЛИЧНОСТТА ⭐
                Placeholder::make('stock_value')
                    ->label('Обща стойност на наличността')
                    ->content(function ($record) {
                        if (!$record || $record->type !== 'product') {
                            return '—';
                        }
                        
                        $totalValue = 0;
                        
                        foreach ($record->variants as $variant) {
                            foreach ($variant->stocks as $stock) {
                                $totalValue += $stock->quantity * $stock->average_cost;
                            }
                        }
                        
                        return number_format($totalValue, 2) . ' €';
                    })
                    ->visible(fn ($get) => $get('type') === 'product'),
                    
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