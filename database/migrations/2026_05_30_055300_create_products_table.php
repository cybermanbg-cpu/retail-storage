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
       Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('sku')->unique();
    $table->text('description')->nullable();
    $table->enum('type', ['product', 'service'])->default('product');
    $table->decimal('base_price', 10, 2); // базова цена (може да се презаписва от варианти)
    $table->decimal('cost', 10, 2)->nullable();
    $table->integer('vat_rate')->default(20);
    $table->boolean('has_variants')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['owner_id', 'sku']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
