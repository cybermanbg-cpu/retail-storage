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
      Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('icon')->nullable(); // икона за POS
    $table->string('color')->nullable(); // цвят за POS
    $table->integer('sort_order')->default(0);
    $table->decimal('default_discount', 5, 2)->default(0); // отстъпка в %
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['owner_id', 'is_active']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
