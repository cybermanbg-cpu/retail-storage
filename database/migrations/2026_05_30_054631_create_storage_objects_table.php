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
       Schema::create('storage_objects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('address')->nullable();
    $table->string('phone')->nullable();
    $table->string('manager_name')->nullable();
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
        Schema::dropIfExists('storage_objects');
    }
};
