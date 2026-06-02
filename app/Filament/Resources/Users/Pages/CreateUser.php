<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ако няма зададена роля, задай 'cashier' по подразбиране
        if (empty($data['roles'])) {
            $data['roles'] = ['cashier'];
        }
        
        return $data;
    }
}