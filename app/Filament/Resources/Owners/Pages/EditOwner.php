<?php

namespace App\Filament\Resources\Owners\Pages;

use App\Filament\Resources\Owners\OwnerResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOwner extends EditRecord
{
    protected static string $resource = OwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
            Action::make('back_to_view')
                ->label('Върни се в преглед')
                ->url(fn() => OwnerResource::getUrl('view', ['record' => $this->getRecord()]))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
