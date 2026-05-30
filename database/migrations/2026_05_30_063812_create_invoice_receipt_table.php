<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('receipt_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Предотвратява дублиране на двойки invoice-receipt
            $table->unique(['invoice_id', 'receipt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_receipt');
    }
};