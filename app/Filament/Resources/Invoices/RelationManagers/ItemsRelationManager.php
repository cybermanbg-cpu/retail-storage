<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Models\ReceiptItem;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'receipts';
    
    protected static ?string $title = 'Продукти';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $receiptIds = $this->getOwnerRecord()->receipts->pluck('id');
                return ReceiptItem::whereIn('receipt_id', $receiptIds);
            })
            ->columns([
                TextColumn::make('receipt.receipt_number')
                    ->label('Разписка')
                    ->searchable(),
                    
                TextColumn::make('product_name_snapshot')
                    ->label('Продукт')
                    ->searchable(),
                    
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('unit_price')
                    ->label('Ед. цена')
                    ->money('BGN'),
                    
                TextColumn::make('total')
                    ->label('Общо')
                    ->money('BGN')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('BGN'),
                    ]),
            ]);
    }
}