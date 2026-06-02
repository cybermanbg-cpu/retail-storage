<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Междинна сума')
                    ->money('€')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount')
                    ->label('Отстъпка')
                    ->money('€')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('vat')
                    ->label('ДДС')
                    ->money('euro')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо ДДС:')
                            ->money('euro'),
                    ]),

                TextColumn::make('total')
                    ->label('Обща сума')
                    ->money('euro')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('euro'),
                    ]),

                // ⭐ СТАТУС С ИМЕ И БАДЖ ⭐
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Чернова',
                        'issued' => 'Издадена',
                        'paid' => 'Платена',
                        'cancelled' => 'Анулирана',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Създадена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Чернова',
                        'issued' => 'Издадена',
                        'paid' => 'Платена',
                        'cancelled' => 'Анулирана',
                    ]),

                SelectFilter::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'name'),

                \Filament\Tables\Filters\Filter::make('issue_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('От дата'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('До дата'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('issue_date', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('issue_date', '<=', $data['to']));
                    }),
            ])
            ->groups([
                Group::make('status')
                    ->label('Статус')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => match ($record->status) {
                        'draft' => 'Чернова',
                        'issued' => 'Издадена',
                        'paid' => 'Платена',
                        'cancelled' => 'Анулирана',
                        default => $record->status,
                    }),

                Group::make('client.name')
                    ->label('Клиент')
                    ->collapsible(),

                Group::make('issue_date')
                    ->label('Дата')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->issue_date->format('d.m.Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->issue_date->format('Y-m-d'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('issue_date', 'desc')),

                Group::make('issue_date')
                    ->label('Седмица')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => 'Седмица ' . $record->issue_date->weekOfYear . ', ' . $record->issue_date->year)
                    ->getKeyFromRecordUsing(fn($record) => $record->issue_date->format('Y') . '-W' . str_pad($record->issue_date->weekOfYear, 2, '0', STR_PAD_LEFT))
                    ->orderQueryUsing(fn($query) => $query->orderBy('issue_date', 'desc')),

                Group::make('issue_date')
                    ->label('Месец')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->issue_date->format('F Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->issue_date->format('Y-m'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('issue_date', 'desc')),

                Group::make('issue_date')
                    ->label('Година')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->issue_date->format('Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->issue_date->format('Y'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('issue_date', 'desc')),
            ])
            ->defaultGroup('issue_date')
            ->groupingSettingsHidden(false)
            ->defaultSort('created_at', 'desc');
    }
}