<?php

namespace App\Filament\Resources\ShoppingSessions\Pages;

use App\Filament\Resources\ShoppingSessions\ShoppingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppingSession extends EditRecord
{
    protected static string $resource = ShoppingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
