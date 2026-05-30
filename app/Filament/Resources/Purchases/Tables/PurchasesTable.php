<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('storageObject.name')
                    ->label('Обект')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('supplier.name')
                    ->label('Доставчик')
                    ->searchable()
                    ->placeholder('—'),
                
                TextColumn::make('purchase_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                
                TextColumn::make('items_count')
                    ->label('Брой артикули')
                    ->counts('items')
                    ->sortable(),
                
                TextColumn::make('total')
                    ->label('Сума')
                    ->money('BGN')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Чернова',
                        'completed' => 'Завършена',
                        'cancelled' => 'Анулирана',
                        default => $state,
                    }),
                
                TextColumn::make('created_at')
                    ->label('Създаден')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('storage_object_id')
                    ->label('Обект')
                    ->relationship('storageObject', 'name'),
                
                SelectFilter::make('supplier_id')
                    ->label('Доставчик')
                    ->relationship('supplier', 'name'),
                
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Чернова',
                        'completed' => 'Завършена',
                        'cancelled' => 'Анулирана',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft'),
                
                Action::make('complete')
                    ->label('Завърши')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Завършване на покупка')
                    ->modalDescription('След завършване, покупката ще бъде обработена и наличността ще бъде актуализирана. Това действие не може да бъде отменено!')
                    ->modalSubmitActionLabel('Да, завърши')
                    ->action(function ($record) {
                        $record->status = 'completed';
                        $record->save();
                        
                        // Тук добави логика за актуализиране на наличността
                        // и себестойността
                        \Filament\Notifications\Notification::make()
                            ->title('Покупката е завършена')
                            ->success()
                            ->send();
                    }),
                
                Action::make('cancel')
                    ->label('Анулирай')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Анулиране на покупка')
                    ->modalDescription('Сигурни ли сте, че искате да анулирате тази покупка?')
                    ->action(function ($record) {
                        $record->status = 'cancelled';
                        $record->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Покупката е анулирана')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super_admin') || 
                                          Auth::user()->hasRole('owner'))
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}