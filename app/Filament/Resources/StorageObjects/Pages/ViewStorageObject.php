<?php

namespace App\Filament\Resources\StorageObjects\Pages;

use App\Filament\Resources\Storageobjects\StorageobjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStorageObject extends ViewRecord
{
    protected static string $resource = StorageobjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
