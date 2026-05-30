<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Select::make('product_variant_id')
                    ->label('Продукт / Вариант')
                    ->relationship('productVariant', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        $record->product->name . 
                        ($record->color ? ' - ' . $record->color->name : '') .
                        ($record->size ? ' / ' . $record->size->name : '')
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Select::make('storage_object_id')
                    ->label('Обект')
                    ->relationship('storageObject', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->integer()
                    ->minValue(0),
                    
                TextInput::make('reserved_quantity')
                    ->label('Резервирано количество')
                    ->numeric()
                    ->default(0)
                    ->integer()
                    ->minValue(0),
                    
                TextInput::make('min_quantity')
                    ->label('Минимално количество')
                    ->numeric()
                    ->default(0)
                    ->integer()
                    ->minValue(0)
                    ->helperText('При достигане на това количество, системата ще предупреждава'),
            ]);
    }
}
