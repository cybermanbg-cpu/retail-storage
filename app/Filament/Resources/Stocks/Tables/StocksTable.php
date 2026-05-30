<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productVariant.product.name')
                    ->label('Продукт')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('productVariant.color.name')
                    ->label('Цвят')
                    ->placeholder('—'),
                    
                TextColumn::make('productVariant.size.name')
                    ->label('Размер')
                    ->placeholder('—'),
                    
                TextColumn::make('storageObject.name')
                    ->label('Обект')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success'),
                    
                TextColumn::make('reserved_quantity')
                    ->label('Резервирано'),
                    
                TextColumn::make('available')
                    ->label('Свободно')
                    ->state(fn ($record) => $record->available)
                    ->numeric()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning'),
                    
                TextColumn::make('min_quantity')
                    ->label('Мин. ниво')
                    ->numeric(),
                    
                TextColumn::make('is_low_stock')
                    ->label('Нисък запас')
                    ->state(fn ($record) => $record->is_low_stock ? '⚠️ Да' : '✅ Не')
                    ->badge()
                    ->color(fn ($state) => $state === '⚠️ Да' ? 'danger' : 'success'),
            ])
            ->filters([
                 SelectFilter::make('storage_object_id')
                    ->label('Обект')
                    ->relationship('storageObject', 'name'),
                    
                SelectFilter::make('product_variant_id')
                    ->label('Продукт')
                    ->relationship('productVariant.product', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
