<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // create_product_variants_table.php
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
    $table->string('sku_suffix')->nullable(); // добавя се към основния SKU
    $table->decimal('price_adjustment', 10, 2)->default(0); // +/- спрямо базовата цена
    $table->string('external_id')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique(['product_id', 'color_id', 'size_id'], 'variant_unique');
    $table->index(['product_id', 'is_active']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
