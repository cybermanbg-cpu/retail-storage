<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Ако има избрана роля
        if (isset($this->data['role']) && !empty($this->data['role'])) {
            // Синхронизиране на една роля
            $this->record->syncRoles([$this->data['role']]);
        }
    }
}