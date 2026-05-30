<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_object_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('min_quantity')->default(0);
            $table->timestamps();
            
            $table->unique(['product_variant_id', 'storage_object_id'], 'stock_unique');
            $table->index(['storage_object_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};