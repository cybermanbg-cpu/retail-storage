<?php

namespace App\Filament\Resources\StorageObjects\Pages;

use App\Filament\Resources\StorageObjects\StorageObjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStorageObjects extends ListRecords
{
    protected static string $resource = StorageObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
