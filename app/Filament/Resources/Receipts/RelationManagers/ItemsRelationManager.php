<?php

namespace App\Filament\Resources\Receipts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    
    protected static ?string $title = 'Продукти';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name_snapshot')
                    ->label('Продукт')
                    ->searchable(),
                    
                TextColumn::make('color_name')
                    ->label('Цвят')
                    ->placeholder('—'),
                    
                TextColumn::make('size_name')
                    ->label('Размер')
                    ->placeholder('—'),
                    
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('unit_price')
                    ->label('Ед. цена')
                    ->money('euro'),
                    
                TextColumn::make('total')
                    ->label('Общо')
                    ->money('euro')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('euro'),
                    ]),
                    
                TextColumn::make('unit_of_measure_snapshot')
                    ->label('М. ед.')
                    ->placeholder('бр.'),
            ]);
    }
}