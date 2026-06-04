<?php
// database/migrations/2024_01_01_000001_create_shopping_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopping_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 20)->unique();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['active', 'payment_pending', 'completed', 'cancelled'])->default('active');
            
            // Връзки към съществуващите таблици
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Финансова информация
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0);
            
            // Метод на плащане
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer'])->nullable();
            
            // Времеви полета
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Индекси за бързо търсене
            $table->index(['session_token', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopping_sessions');
    }
};