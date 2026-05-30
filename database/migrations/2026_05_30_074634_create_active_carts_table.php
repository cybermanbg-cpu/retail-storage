<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('cart_name')->default('Нова продажба');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('storage_object_id')->constrained();
            $table->json('items')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_carts');
    }
};