<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $isSuperAdmin = Auth::user() && Auth::user()->hasRole('super_admin');
        
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Име')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('Имейл')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                    
                TextColumn::make('owner.name')
                    ->label('Собственик')
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),
                    
                // ⭐ НОВА КОЛОНА ЗА СКЛАДОВ ОБЕКТ ⭐
                TextColumn::make('storageObject.name')
                    ->label('Складов обект')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->tooltip('Складът, в който работи потребителят')
                    ->color(fn($state) => $state && $state !== '-' ? 'success' : 'gray')
                    ->icon(fn($state) => $state && $state !== '-' ? 'heroicon-o-building-storefront' : 'heroicon-o-question-mark-circle'),
                    
                TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'owner' => 'warning',
                        'manager' => 'info',
                        'cashier' => 'success',
                        'kiosk' => 'primary',  // ⭐ ДОБАВЕНА РОЛЯ ЗА ЩАНД
                        default => 'gray',
                    })
                    ->searchable(),
                    
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('Създаден')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->visible($isSuperAdmin),
                    
                // ⭐ НОВ ФИЛТЪР ЗА СКЛАДОВ ОБЕКТ ⭐
                SelectFilter::make('storage_object_id')
                    ->label('Складов обект')
                    ->relationship('storageObject', 'name')
                    ->visible($isSuperAdmin),
                    
                SelectFilter::make('roles')
                    ->label('Роля')
                    ->relationship('roles', 'name')
                    ->multiple(),
                    
                SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        '1' => 'Активен',
                        '0' => 'Неактивен',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}