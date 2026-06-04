<?php

namespace App\Filament\Resources\ShoppingSessionItems\Pages;

use App\Filament\Resources\ShoppingSessionItems\ShoppingSessionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppingSessionItem extends EditRecord
{
    protected static string $resource = ShoppingSessionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
