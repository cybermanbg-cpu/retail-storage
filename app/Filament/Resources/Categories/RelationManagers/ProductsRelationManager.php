<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $title = 'Продукти в категорията';

    /**
     * Филтриране на продуктите според ролята
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
            return $query->where('owner_id', $user->owner_id);
        }
        
        return $query->whereRaw('0 = 1');
    }
    
    /**
     * Кой може да добавя продукти към категорията
     */
    public function canAttach(): bool
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
     * Кой може да премахва продукти от категорията
     */
    public function canDetach($record): bool
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Име')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable(),
                    
                TextColumn::make('base_price')
                    ->label('Цена')
                    ->money('BGN'),
                    
                TextColumn::make('unitOfMeasure.symbol')
                    ->label('М. ед.')
                    ->placeholder('бр.'),
                    
                TextColumn::make('discount_from_category')
                    ->label('Отстъпка')
                    ->state(fn($record) => $this->getOwnerRecord()->default_discount)
                    ->suffix('%')
                    ->badge()
                    ->color('warning'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Добави продукт')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn($query) => 
                        $query->where('type', 'product')
                              ->where('is_active', true)
                    ),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Премахни'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Премахни избраните'),
                ]),
            ]);
    }
}