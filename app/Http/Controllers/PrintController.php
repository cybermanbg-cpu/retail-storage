<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Печат на стокова разписка
     */
    public function printReceipt($id)
    {
        $receipt = Receipt::with(['items', 'client', 'storageObject', 'user'])
            ->findOrFail($id);
        
        $pdf = Pdf::loadView('prints.receipt', compact('receipt'));
        
        return $pdf->stream('разписка-' . $receipt->receipt_number . '.pdf');
    }
    
    /**
     * Печат на фактура
     */
    public function printInvoice($id)
    {
        $invoice = Invoice::with(['client', 'receipts.items', 'owner'])
            ->findOrFail($id);
        
        $pdf = Pdf::loadView('prints.invoice', compact('invoice'));
        
        return $pdf->stream('фактура-' . $invoice->invoice_number . '.pdf');
    }
}