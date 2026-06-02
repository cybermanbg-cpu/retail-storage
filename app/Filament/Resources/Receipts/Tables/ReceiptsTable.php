<?php

namespace App\Filament\Resources\Receipts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole('super_admin') || $user->hasRole('owner') || $user->hasRole('manager'));
        $canEdit = $user && ($user->hasRole('super_admin') || $user->hasRole('owner') || $user->hasRole('manager'));
        $canDelete = $user && $user->hasRole('super_admin');

        return $table
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Клиент')
                    ->searchable()
                    ->placeholder('Анонимен'),

                TextColumn::make('storageObject.name')
                    ->label('Обект')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Обща сума')
                    ->money('euro')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Общо:')
                            ->money('euro'),
                    ]),

                TextColumn::make('payment_method')
                    ->label('Плащане')
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('is_invoiced')
                    ->label('Фактурирана')
                    ->badge()
                    ->state(fn($record) => $record->is_invoiced ? '✅ Да' : '❌ Не')
                    ->color(fn($record) => $record->is_invoiced ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('storage_object_id')
                    ->label('Обект')
                    ->relationship('storageObject', 'name'),

                SelectFilter::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'name'),

                SelectFilter::make('payment_method')
                    ->label('Начин на плащане')
                    ->options([
                        'cash' => 'В брой',
                        'card' => 'Карта',
                        'bank_transfer' => 'Банков превод',
                    ]),

                SelectFilter::make('is_invoiced')
                    ->label('Фактурирана')
                    ->options([
                        '0' => 'Не',
                        '1' => 'Да',
                    ]),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('От дата'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('До дата'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('created_at', '<=', $data['to']));
                    }),
            ])
            ->groups([
                Group::make('user.name')
                    ->label('Касиер')
                    ->collapsible(),

                Group::make('client.name')
                    ->label('Клиент'),

                Group::make('storageObject.name')
                    ->label('Обект')
                    ->collapsible(),

                // === Групиране по дата (сортирани от най-нова към най-стара) ===
                Group::make('created_at')
                    ->label('Дата')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->created_at->format('d.m.Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->created_at->format('Y-m-d'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('created_at', 'desc')),   // ← сортиране

                Group::make('created_at')
                    ->label('Седмица')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => 'Седмица ' . $record->created_at->weekOfYear . ', ' . $record->created_at->year)
                    ->getKeyFromRecordUsing(fn($record) => $record->created_at->format('Y') . '-W' . str_pad($record->created_at->weekOfYear, 2, '0', STR_PAD_LEFT))
                    ->orderQueryUsing(fn($query) => $query->orderBy('created_at', 'desc')),   // ← сортиране

                Group::make('created_at')
                    ->label('Месец')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->created_at->format('F Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->created_at->format('Y-m'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('created_at', 'desc')),   // ← сортиране

                Group::make('created_at')
                    ->label('Година')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn($record) => $record->created_at->format('Y'))
                    ->getKeyFromRecordUsing(fn($record) => $record->created_at->format('Y'))
                    ->orderQueryUsing(fn($query) => $query->orderBy('created_at', 'desc')),   // ← сортиране
            ])
            ->defaultGroup('created_at')           // По подразбиране ще показва "Дата"
            ->groupingSettingsHidden(false)
            ->defaultSort('created_at', 'desc')    // Сортиране на записите вътре в групите
            ->recordActions([
                // ⭐ VIEW ACTION – всички могат да преглеждат ⭐
                ViewAction::make()
                    ->label('Преглед')
                    ->icon('heroicon-o-eye'),

                // ⭐ EDIT ACTION – само администратори ⭐
                EditAction::make()
                    ->visible($canEdit),

                // ⭐ CREATE INVOICE – само за не фактурирани ⭐
                Action::make('create_invoice')
                    ->label('Създай фактура')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_invoiced && $record->client_id)
                    ->form([
                        \Filament\Forms\Components\Select::make('selected_receipts')
                            ->label('Избери разписки за фактуриране')
                            ->multiple()
                            ->options(function ($record) {
                                // Взима всички нефактурирани разписки на същия клиент
                                return \App\Models\Receipt::where('client_id', $record->client_id)
                                    ->where('is_invoiced', false)
                                    ->where('owner_id', $record->owner_id)
                                    ->get()
                                    ->mapWithKeys(fn($r) => [$r->id => $r->receipt_number . ' - ' . number_format($r->total_amount + $r->total_vat, 2) . ' лв.']);
                            })
                            ->required()
                            ->helperText('Само разписки на един и същ клиент могат да се обединят'),

                        \Filament\Forms\Components\DatePicker::make('issue_date')
                            ->label('Дата на издаване')
                            ->required()
                            ->default(now()),

                        \Filament\Forms\Components\DatePicker::make('due_date')
                            ->label('Падеж')
                            ->nullable(),
                    ])
                    ->action(function ($record, array $data) {
                        $ownerId = $record->owner_id;
                        $clientId = $record->client_id;

                        // Вземаме избраните разписки
                        $selectedReceipts = \App\Models\Receipt::whereIn('id', $data['selected_receipts'])
                            ->where('client_id', $clientId)
                            ->where('is_invoiced', false)
                            ->get();

                        if ($selectedReceipts->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Грешка!')
                                ->body('Няма избрани разписки')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Изчисляване на сумите
                        $subtotal = $selectedReceipts->sum('total_amount');
                        $totalVat = $selectedReceipts->sum('total_vat');
                        $total = $subtotal + $totalVat;

                        // Създаване на фактура
                        $invoice = \App\Models\Invoice::create([
                            'owner_id' => $ownerId,
                            'client_id' => $clientId,
                            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber($ownerId),
                            'issue_date' => $data['issue_date'],
                            'due_date' => $data['due_date'],
                            'subtotal' => $subtotal,
                            'discount' => 0,
                            'vat' => $totalVat,
                            'total' => $total,
                            'status' => 'draft',
                        ]);

                        // Прикачване на разписките
                        $invoice->receipts()->attach($selectedReceipts->pluck('id'));

                        // Маркиране на разписките като фактурирани
                        foreach ($selectedReceipts as $receipt) {
                            $receipt->is_invoiced = true;
                            $receipt->save();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Фактурата е създадена!')
                            ->body('Номер: ' . $invoice->invoice_number . ' | Разписки: ' . $selectedReceipts->count() . ' | Сума: ' . number_format($total, 2) . ' лв.')
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.invoices.edit', $invoice);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible($canDelete),

                    BulkAction::make('bulk_create_invoice')
                        ->label('Създай фактура за избраните')
                        ->icon('heroicon-o-document-plus')
                        ->color('success')
                        ->form([
                            \Filament\Forms\Components\Select::make('client_id')
                                ->label('Клиент')
                                ->options(function ($records) {
                                    // Показва само клиенти от избраните разписки
                                    $clientIds = $records->pluck('client_id')->unique();
                                    return \App\Models\Client::whereIn('id', $clientIds)
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->helperText('Всички избрани разписки трябва да са за този клиент'),

                            \Filament\Forms\Components\DatePicker::make('issue_date')
                                ->label('Дата на издаване')
                                ->required()
                                ->default(now()),

                            \Filament\Forms\Components\DatePicker::make('due_date')
                                ->label('Падеж')
                                ->nullable(),
                        ])
                        ->action(function ($records, array $data) {
                            $ownerId = auth()->user()->owner_id ?? 1;
                            $clientId = $data['client_id'];

                            // Филтрира разписките за избрания клиент
                            $selectedReceipts = $records->filter(fn($r) => $r->client_id == $clientId && !$r->is_invoiced);

                            if ($selectedReceipts->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Грешка!')
                                    ->body('Няма валидни разписки за избрания клиент')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $subtotal = $selectedReceipts->sum('total_amount');
                            $totalVat = $selectedReceipts->sum('total_vat');
                            $total = $subtotal + $totalVat;

                            $invoice = \App\Models\Invoice::create([
                                'owner_id' => $ownerId,
                                'client_id' => $clientId,
                                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber($ownerId),
                                'issue_date' => $data['issue_date'],
                                'due_date' => $data['due_date'],
                                'subtotal' => $subtotal,
                                'discount' => 0,
                                'vat' => $totalVat,
                                'total' => $total,
                                'status' => 'draft',
                            ]);

                            $invoice->receipts()->attach($selectedReceipts->pluck('id'));

                            foreach ($selectedReceipts as $receipt) {
                                $receipt->is_invoiced = true;
                                $receipt->save();
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Фактурата е създадена!')
                                ->body('Номер: ' . $invoice->invoice_number)
                                ->success()
                                ->send();

                            return redirect()->route('filament.admin.resources.invoices.edit', $invoice);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Създаване на фактура')
                        ->modalDescription('Избраните разписки ще бъдат обединени в една фактура. Всички те трябва да са за един и същ клиент.')
                        ->modalSubmitActionLabel('Създай'),
                ]),
            ]);
    }
}