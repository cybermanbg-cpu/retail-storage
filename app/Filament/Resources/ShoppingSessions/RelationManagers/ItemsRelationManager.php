<?php

namespace App\Filament\Resources\ShoppingSessions\RelationManagers;

use App\Models\ShoppingSessionItem;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    
    protected static ?string $title = 'Артикули';
    
    protected static ?string $recordTitleAttribute = 'product_name';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $sessionId = $this->getOwnerRecord()->id;
                return ShoppingSessionItem::where('shopping_session_id', $sessionId);
            })
            ->columns([
                TextColumn::make('product_name')
                    ->label('Продукт')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(3)
                    ->sortable(),
                    
                TextColumn::make('unit_price')
                    ->label('Ед. цена')
                    ->money('EUR')
                    ->sortable(),
                    
                TextColumn::make('total_price')
                    ->label('Общо')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('EUR'),
                    ]),
                    
                TextColumn::make('kiosk.name')
                    ->label('Щанд')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('unit')
                    ->label('Мерна единица')
                    ->searchable(),
                    
                TextColumn::make('created_at')
                    ->label('Добавен на')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('kiosk_id')
                    ->label('Щанд')
                    ->relationship('kiosk', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}