<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Номера на фактура не трябва да се променя
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Увери се, че числовите полета са числа
        $data['subtotal'] = floatval($data['subtotal'] ?? 0);
        $data['discount'] = floatval($data['discount'] ?? 0);
        $data['vat'] = floatval($data['vat'] ?? 0);
        $data['total'] = floatval($data['total'] ?? 0);
        
        return $data;
    }

   protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $user = Auth::user();

        $actions = [];

        $actions[] = Action::make('back_to_view')
            ->label('Върни се в преглед')
            ->url(fn() => InvoiceResource::getUrl('view', ['record' => $record]))
            ->color('gray')
            ->icon('heroicon-o-arrow-left');

        if ($record->status === 'draft' || $user->hasRole('admin') || $user->hasRole('super_admin')) {
            
            $actions[] = Action::make('save_and_continue')
                ->label('Запази и продължи')
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->action('save');

            // if ($record->status === 'draft') {
            //     $actions[] = Action::make('complete')
            //         ->label('Завърши покупката')
            //         ->icon('heroicon-o-check-circle')
            //         ->color('success')
            //         ->requiresConfirmation()
            //         ->action(function () {
            //             $record = $this->getRecord();
            //             $record->status = 'completed';
            //             $record->save();

            //             \Filament\Notifications\Notification::make()
            //                 ->title('Покупката е завършена успешно')
            //                 ->success()
            //                 ->send();

            //             $this->redirect(InvoiceResource::getUrl('view', ['record' => $record]));
            //         });
            // }

            if ($record->status === 'draft') {
                $actions[] = DeleteAction::make();
            }
        }

        return $actions;
    }
}