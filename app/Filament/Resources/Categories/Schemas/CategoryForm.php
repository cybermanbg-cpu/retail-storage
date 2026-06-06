<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use TangoDevIt\FilamentEmojiPicker\EmojiPickerAction;

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

                // TextInput::make('icon')
                //     ->label('Икона (Font Awesome)')
                //     ->placeholder('fas fa-pizza-slice')
                //     ->maxLength(50),
                TextInput::make('icon')
                    ->label('Икона (emoji) Избрете емоджи иконка за категорията')
                    ->placeholder('🍎')
                    ->maxLength(255)
                    ->helperText('Използвайте емоджи иконка за категорията')
                    // Добавяне на бутон за избор на емоджи
                    ->suffixAction(
                        EmojiPickerAction::make('emoji-icon')
                            ->label('Избери емоджи')
                            ->icon('heroicon-o-face-smile')
                            ->popupPlacement('bottom-end')
                    ),

                ColorPicker::make('color')
                    ->label('Цвят')
                    ->helperText('Цвят за акцентиране на категорията'),

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

                Toggle::make('show_in_restaurant_pos')
                    ->label('Показва се в Restaurant POS')
                    ->default(true)
                    ->helperText('Ако е активно, категорията ще се показва в Restaurant POS интерфейса'),
            ]);
    }
}