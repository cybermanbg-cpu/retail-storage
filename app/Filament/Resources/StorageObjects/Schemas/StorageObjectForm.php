<?php

namespace App\Filament\Resources\StorageObjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

class StorageObjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label('Собственик')
                    ->relationship('owner', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                TextInput::make('name')
                    ->label('Име на обект')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(500),
                    
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(20),
                    
                TextInput::make('manager_name')
                    ->label('Отговорник')
                    ->maxLength(255),
                    
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
