<?php

namespace App\Filament\Resources\StorageObjects\Pages;

use App\Filament\Resources\StorageObjects\StorageObjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorageObject extends EditRecord
{
    protected static string $resource = StorageObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
