<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                  TextColumn::make('owner.name')
                    ->label('Собственик')
                    ->searchable()
                    ->toggleable(),
                    
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable(),
                    
                TextColumn::make('name')
                    ->label('Име')
                    ->searchable(),
                    
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => 
                        $state === 'product' ? '📦 Продукт' : '⚙️ Услуга'
                    )
                    ->color(fn (string $state): string => 
                        $state === 'product' ? 'info' : 'success'
                    ),
                    
                TextColumn::make('base_price')
                    ->label('Цена')
                    ->money('BGN')
                    ->sortable(),
                    
                TextColumn::make('vat_rate')
                    ->label('ДДС')
                    ->suffix('%'),
                    
                IconColumn::make('has_variants')
                    ->label('Варианти')
                    ->boolean(),
                    
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                 SelectFilter::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name'),
                    
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'product' => 'Продукт',
                        'service' => 'Услуга',
                    ]),
                    
                SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        '1' => 'Активен',
                        '0' => 'Неактивен',
                    ]),
            ])
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
