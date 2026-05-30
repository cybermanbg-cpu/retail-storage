<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_object_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');
            $table->date('invoice_date')->nullable();
            $table->string('supplier_invoice')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_cost', 12, 2)->default(0);
            $table->decimal('vat', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['owner_id', 'purchase_date']);
            $table->index(['purchase_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};