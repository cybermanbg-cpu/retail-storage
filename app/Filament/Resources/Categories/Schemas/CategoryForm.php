<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->required()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                    
                TextInput::make('name')
                    ->label('Име')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('slug')
                    ->label('URL слаг')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                TextInput::make('icon')
                    ->label('Икона (Font Awesome)')
                    ->placeholder('fas fa-pizza-slice')
                    ->maxLength(50),
                    
                TextInput::make('color')
                    ->label('Цвят')
                    ->placeholder('bg-red-500')
                    ->maxLength(50),
                    
                TextInput::make('sort_order')
                    ->label('Ред на показване')
                    ->numeric()
                    ->default(0),
                    
                TextInput::make('default_discount')
                    ->label('Отстъпка (%)')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%')
                    ->default(0),
                    
                Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),
                    
                // ⭐ НОВО ПОЛЕ ЗА RESTAURANT POS ⭐
                Toggle::make('show_in_restaurant_pos')
                    ->label('Показва се в Restaurant POS')
                    ->default(true)
                    ->helperText('Ако е активно, категорията ще се показва в Restaurant POS интерфейса'),
            ]);
    }
}