<?php

namespace App\Observers;

use App\Models\ActiveCart;
use Illuminate\Support\Facades\Log;

class ActiveCartObserver
{
    /**
     * Handle the ActiveCart "created" event.
     */
    public function created(ActiveCart $activeCart): void
    {
        Log::info('Нова количка създадена', [
            'cart_id' => $activeCart->id,
            'user_id' => $activeCart->user_id,
            'cart_name' => $activeCart->cart_name,
        ]);
    }

    /**
     * Handle the ActiveCart "updated" event.
     */
    public function updated(ActiveCart $activeCart): void
    {
        // Може да се използва за реално време синхронизация
    }

    /**
     * Handle the ActiveCart "deleted" event.
     */
    public function deleted(ActiveCart $activeCart): void
    {
        Log::info('Количка изтрита', [
            'cart_id' => $activeCart->id,
            'user_id' => $activeCart->user_id,
        ]);
    }
}