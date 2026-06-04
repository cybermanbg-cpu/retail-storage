<?php

namespace App\Filament\Resources\ShoppingSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShoppingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('session_token')
                    ->label('Токен')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Токенът е копиран')
                    ->fontFamily('mono')
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Сума')
                    ->money('EUR')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('items_count')
                    ->label('Артикули')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'payment_pending' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Активна',
                        'payment_pending' => 'Чака плащане',
                        'completed' => 'Завършена',
                        'cancelled' => 'Анулирана',
                        default => $state,
                    }),

                TextColumn::make('createdBy.name')
                    ->label('Създадена от')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Създадена на')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Платена на')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
               SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активна',
                        'payment_pending' => 'Чака плащане',
                        'completed' => 'Завършена',
                        'cancelled' => 'Анулирана',
                    ]),

               SelectFilter::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload(),

               Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('От дата'),
                        DatePicker::make('created_until')
                            ->label('До дата'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
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
