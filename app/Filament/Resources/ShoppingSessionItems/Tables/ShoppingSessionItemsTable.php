<?php

namespace App\Filament\Resources\ShoppingSessionItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShoppingSessionItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shoppingSession.session_token')
                    ->label('Токен на сметка')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Токенът е копиран')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->sortable(),

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
                    ->color('success'),

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
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('shopping_session_id')
                    ->label('Сметка')
                    ->relationship('shoppingSession', 'session_token')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kiosk_id')
                    ->label('Щанд')
                    ->relationship('kiosk', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product_id')
                    ->label('Продукт')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])->defaultSort('created_at', 'desc')
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