<?php

namespace App\Filament\Resources\Owners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('name')
                    ->label('Име')
                    ->searchable(),
                    
                TextColumn::make('company_name')
                    ->label('Фирма')
                    ->searchable(),
                    
                TextColumn::make('email')
                    ->label('Имейл')
                    ->searchable(),
                    
                TextColumn::make('phone')
                    ->label('Телефон'),
                    
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                    
                TextColumn::make('created_at')
                    ->label('Създаден')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                 SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        '1' => 'Активен',
                        '0' => 'Неактивен',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
