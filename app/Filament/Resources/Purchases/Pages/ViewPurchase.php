<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $user = Auth::user();
        
        $actions = [];
        
        // Edit action - показва се ако може да се редактира
        if (PurchaseResource::canEdit($record)) {
            $actions[] = Actions\EditAction::make();
        }
        
        // Завършване на покупката (само за чернови)
        if ($record->status === 'draft' && PurchaseResource::canEdit($record)) {
            $actions[] = Actions\Action::make('complete')
                ->label('Завърши')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Завършване на покупка')
                ->modalDescription('След завършване, покупката ще бъде обработена и наличността ще бъде актуализирана. Това действие не може да бъде отменено!')
                ->action(function () use ($record) {
                    $record->status = 'completed';
                    $record->save();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Покупката е завършена')
                        ->success()
                        ->send();
                        
                    $this->redirect(PurchaseResource::getUrl('view', ['record' => $record]));
                });
        }
        
        // Действия за администратор при завършена покупка
        if ($record->status === 'completed' && 
            ($user->hasRole('admin') || $user->hasRole('super_admin'))) {
            
            $actions[] = Actions\Action::make('revert_to_draft')
                ->label('Върни в чернова')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Връщане в чернова')
                ->modalDescription('Внимание! Това действие ще върне покупката в статус "чернова" и ще трябва да актуализирате наличността ръчно. Сигурни ли сте?')
                ->action(function () use ($record) {
                    $record->status = 'draft';
                    $record->save();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Покупката е върната в чернова')
                        ->warning()
                        ->send();
                        
                    $this->redirect(PurchaseResource::getUrl('edit', ['record' => $record]));
                });
        }
        
        return $actions;
    }
    
    // Защита на достъпа до view страницата
    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();
        
        if (!PurchaseResource::canView($record)) {
            abort(403, 'Нямате достъп до тази покупка.');
        }
    }
}