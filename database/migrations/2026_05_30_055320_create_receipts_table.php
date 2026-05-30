<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_object_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_number')->unique();
            $table->enum('type', ['sale', 'receipt', 'write_off', 'transfer', 'inventory']);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('total_vat', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_invoiced')->default(false);
            $table->timestamps();
            
            $table->index(['owner_id', 'type', 'created_at']);
            $table->index(['receipt_number']);
            $table->index(['storage_object_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};