<?php

namespace App\Filament\Resources\StorageObjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StorageObjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('owner.name')
                    ->label('Собственик')
                    ->searchable(),
                    
                TextColumn::make('name')
                    ->label('Име на обект')
                    ->searchable(),
                    
                TextColumn::make('address')
                    ->label('Адрес'),
                    
                TextColumn::make('phone')
                    ->label('Телефон'),
                    
                TextColumn::make('manager_name')
                    ->label('Отговорник'),
                    
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                    
                TextColumn::make('created_at')
                    ->label('Създаден')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                SelectFilter::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name'),
                    
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
