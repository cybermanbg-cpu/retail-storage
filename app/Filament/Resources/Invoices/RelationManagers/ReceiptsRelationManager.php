<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'receipts';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('receipt_number')
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Номер на бележка')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Клиент')
                    ->placeholder('Анонимен')
                    ->searchable(),

                TextColumn::make('storageObject.name')
                    ->label('Обект')
                    ->searchable(),

                // ⭐ СУМИРАНЕ НА СУМАТА ⭐
                TextColumn::make('total_amount')
                    ->label('Сума')
                    ->money('BGN')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('BGN'),
                    ]),

                TextColumn::make('payment_method')
                    ->label('Плащане')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'В брой',
                        'card' => 'Карта',
                        'bank_transfer' => 'Банков превод',
                        default => $state ?? '—',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'bank_transfer' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Касиер')
                    ->searchable(),
            ])
            ->defaultSort('receipts.created_at', 'desc')
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->label('Преглед'),
            ])
            ->bulkActions([]);
    }
}