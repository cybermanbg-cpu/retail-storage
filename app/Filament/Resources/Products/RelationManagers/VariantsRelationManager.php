<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    
    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Варианти (цвят/размер)';

    /**
     * Филтриране на вариантите според ролята на логнатия потребител
     */
    public function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        
        if ($user->owner_id) {
            return $query->whereHas('product', function ($q) use ($user) {
                $q->where('owner_id', $user->owner_id);
            });
        }
        
        return $query->whereRaw('0 = 1');
    }
    
    /**
     * Кой може да създава варианти (нестатичен метод)
     */
    public function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да редактира вариант (нестатичен метод)
     */
    public function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да изтрива вариант (нестатичен метод)
     */
    public function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        if ($user->hasRole('manager')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || $user->hasRole('owner');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('color_id')
                    ->label('Цвят')
                    ->relationship('color', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),

                Select::make('size_id')
                    ->label('Размер')
                    ->relationship('size', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),

                TextInput::make('sku_suffix')
                    ->label('SKU суфикс')
                    ->helperText('Добавя се към основния SKU (напр. -RED-M)')
                    ->maxLength(50),

                TextInput::make('price_adjustment')
                    ->label('Корекция на цената')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€')
                    ->helperText('Положителна или отрицателна стойност'),

                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('color.name')
                    ->label('Цвят')
                    ->placeholder('—'),

                TextColumn::make('size.name')
                    ->label('Размер')
                    ->placeholder('—'),

                TextColumn::make('sku_suffix')
                    ->label('SKU суфикс')
                    ->placeholder('—'),

                TextColumn::make('full_sku')
                    ->label('Пълен SKU')
                    ->state(fn($record) => $record->full_sku),

                // ⭐ ЦЕНА НА ВАРИАНТА – изчислена на място ⭐
                TextColumn::make('final_price')
                    ->label('Цена')
                    ->money('euro')
                    ->getStateUsing(function ($record) {
                        $basePrice = $record->product->base_price ?? 0;
                        $adjustment = $record->price_adjustment ?? 0;
                        return $basePrice + $adjustment;
                    }),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}