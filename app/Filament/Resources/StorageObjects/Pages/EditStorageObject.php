<?php

namespace App\Filament\Resources\StorageObjects\Pages;

use App\Filament\Resources\StorageObjects\StorageObjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorageObject extends EditRecord
{
    protected static string $resource = StorageObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
               Action::make('back_to_view')
                ->label('Върни се в преглед')
                ->url(fn() => StorageObjectResource::getUrl('view', ['record' => $this->getRecord()]))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            
            // Бутон "Запази и продължи" (опционален)
            Action::make('save_and_continue')
                ->label('Запази и продължи')
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->action('save'),
        ];
    }
}
