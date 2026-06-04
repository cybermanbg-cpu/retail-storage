<?php

namespace App\Filament\Resources\ShoppingSessions\Pages;

use App\Filament\Resources\ShoppingSessions\ShoppingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppingSessions extends ListRecords
{
    protected static string $resource = ShoppingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
