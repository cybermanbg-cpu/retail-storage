<?php

namespace App\Filament\Resources\ShoppingSessionItems\Pages;

use App\Filament\Resources\ShoppingSessionItems\ShoppingSessionItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppingSessionItems extends ListRecords
{
    protected static string $resource = ShoppingSessionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
