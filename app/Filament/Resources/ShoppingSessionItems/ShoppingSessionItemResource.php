<?php

namespace App\Filament\Resources\ShoppingSessionItems;

use App\Filament\Resources\ShoppingSessionItems\Pages\CreateShoppingSessionItem;
use App\Filament\Resources\ShoppingSessionItems\Pages\EditShoppingSessionItem;
use App\Filament\Resources\ShoppingSessionItems\Pages\ListShoppingSessionItems;
use App\Filament\Resources\ShoppingSessionItems\Schemas\ShoppingSessionItemForm;
use App\Filament\Resources\ShoppingSessionItems\Tables\ShoppingSessionItemsTable;
use App\Models\ShoppingSessionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoppingSessionItemResource extends Resource
{
    protected static ?string $model = ShoppingSessionItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ShoppingSessionItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppingSessionItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppingSessionItems::route('/'),
            'create' => CreateShoppingSessionItem::route('/create'),
            'edit' => EditShoppingSessionItem::route('/{record}/edit'),
        ];
    }
}
