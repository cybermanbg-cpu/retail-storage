<?php

namespace App\Filament\Resources\Owners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OwnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('name')
                    ->label('Име')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('company_name')
                    ->label('Фирма')
                    ->maxLength(255),
                    
                TextInput::make('email')
                    ->label('Имейл')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(20),
                    
                TextInput::make('vat_number')
                    ->label('ЕИК/ДДС номер')
                    ->maxLength(50),
                    
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
