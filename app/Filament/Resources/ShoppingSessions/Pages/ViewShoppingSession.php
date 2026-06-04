<?php

namespace App\Filament\Resources\ShoppingSessions\Pages;

use App\Filament\Resources\ShoppingSessions\ShoppingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShoppingSession extends ViewRecord
{
    protected static string $resource = ShoppingSessionResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        
        $actions = [];
        
        // Само ако сметката е активна, може да се редактира
        if ($record->status === 'active') {
            $actions[] = Actions\EditAction::make();
        }
        
        // Анулиране на сметката (само за активни)
        if ($record->status === 'active') {
            $actions[] = Actions\Action::make('cancel')
                ->label('Анулирай')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Анулиране на сметка')
                ->modalDescription('Сигурни ли сте, че искате да анулирате тази сметка? Това действие не може да бъде отменено!')
                ->action(function () use ($record) {
                    $record->status = 'cancelled';
                    $record->save();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Сметката е анулирана')
                        ->danger()
                        ->send();
                        
                    $this->redirect(ShoppingSessionResource::getUrl('index'));
                });
        }
        
        // Възстановяване на анулирана сметка (само за админи)
        if ($record->status === 'cancelled' && auth()->user()->hasRole(['admin', 'super_admin'])) {
            $actions[] = Actions\Action::make('restore')
                ->label('Възстанови')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Възстановяване на сметка')
                ->modalDescription('Сигурни ли сте, че искате да възстановите тази сметка?')
                ->action(function () use ($record) {
                    $record->status = 'active';
                    $record->save();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Сметката е възстановена')
                        ->success()
                        ->send();
                        
                    $this->redirect(ShoppingSessionResource::getUrl('view', ['record' => $record]));
                });
        }
        
        return $actions;
    }
    
    // Защита на достъпа до view страницата
    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();
        
        // Само ако има права да преглежда
        if (!in_array($record->status, ['active', 'completed', 'cancelled'])) {
            abort(403, 'Нямате достъп до тази сметка.');
        }
    }
}