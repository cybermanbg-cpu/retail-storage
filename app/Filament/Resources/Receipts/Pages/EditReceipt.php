<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditReceipt extends EditRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make()
            //     ->visible(fn() => Auth::user()->hasRole('super_admin')),
        ];
    }
    
    /**
     * Проверка дали потребителят може да редактира този запис
     */
    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        $record = $this->getRecord();
        
        // Само super_admin, owner и manager могат да редактират
        if (!$user->hasRole('super_admin') && 
            !$user->hasRole('owner') && 
            !$user->hasRole('manager')) {
            abort(403, 'Нямате права да редактирате тази разписка.');
        }
        
        // Проверка за собственика (owner и manager виждат само своите)
        if (($user->hasRole('owner') || $user->hasRole('manager')) && $user->owner_id) {
            if ($record->owner_id !== $user->owner_id) {
                abort(403, 'Нямате права да редактирате тази разписка.');
            }
        }
    }
    
    /**
     * Мутиране на данните преди запис
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Защита – само хедърните полета се променят
        // Детайлите (суми, ДДС) не могат да се променят ръчно
        $record = $this->getRecord();
        
        $data['total_amount'] = $record->total_amount;
        $data['total_vat'] = $record->total_vat;
        
        return $data;
    }
    
    /**
     * След запис – синхронизиране на фактурата ако има такава
     */
    protected function afterSave(): void
    {
        $record = $this->getRecord();
        
        // Ако разписката е фактурирана, актуализирай фактурата
        if ($record->is_invoiced && $record->invoices->count() > 0) {
            foreach ($record->invoices as $invoice) {
                $subtotal = $invoice->receipts->sum('total_amount');
                $vat = round($subtotal * 0.20, 2);
                $total = round($subtotal + $vat, 2);
                
                $invoice->update([
                    'subtotal' => $subtotal,
                    'vat' => $vat,
                    'total' => $total,
                ]);
            }
        }
    }
}