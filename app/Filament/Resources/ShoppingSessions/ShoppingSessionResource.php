<?php

namespace App\Filament\Resources\ShoppingSessions;

use App\Filament\Resources\ShoppingSessions\Pages\CreateShoppingSession;
use App\Filament\Resources\ShoppingSessions\Pages\EditShoppingSession;
use App\Filament\Resources\ShoppingSessions\Pages\ListShoppingSessions;
use App\Filament\Resources\ShoppingSessions\Pages\ViewShoppingSession;
use App\Filament\Resources\ShoppingSessions\Schemas\ShoppingSessionForm;
use App\Filament\Resources\ShoppingSessions\Tables\ShoppingSessionsTable;
use App\Models\ShoppingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoppingSessionResource extends Resource
{
    protected static ?string $model = ShoppingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ShoppingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppingSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
             RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppingSessions::route('/'),
            'view' => ViewShoppingSession::route('/{record}'),
            // 'create' => CreateShoppingSession::route('/create'),
            // 'edit' => EditShoppingSession::route('/{record}/edit'),
        ];
    }
}
