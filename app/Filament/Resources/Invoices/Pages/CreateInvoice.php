<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $ownerId = Auth::user()->owner_id ?? 1;

        $data['invoice_number'] = Invoice::generateInvoiceNumber($ownerId);
        $data['owner_id'] = $ownerId;

        $data['subtotal'] = floatval($data['subtotal'] ?? 0);
        $data['discount'] = floatval($data['discount'] ?? 0);
        $data['vat'] = floatval($data['vat'] ?? 0);
        $data['total'] = floatval($data['total'] ?? 0);

        return $data;
    }
}