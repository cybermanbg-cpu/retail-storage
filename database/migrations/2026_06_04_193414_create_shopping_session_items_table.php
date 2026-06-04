<?php
// database/migrations/2024_01_01_000002_create_shopping_session_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopping_session_items', function (Blueprint $table) {
            $table->id();
            
            // Връзки към сесията, щанда и продукта
            $table->foreignId('shopping_session_id')->constrained('shopping_sessions')->onDelete('cascade');
            $table->foreignId('kiosk_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Снимка на продукта (за исторически данни)
            $table->string('product_name', 255);
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('unit', 20)->default('бр.');
            
            // Допълнителна информация
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Индекси за бързо търсене
            $table->index('shopping_session_id');
            $table->index('kiosk_id');
            $table->index('product_id');
            $table->index('created_at');
            
            // Комплексен индекс за често срещани заявки
            $table->index(['shopping_session_id', 'kiosk_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopping_session_items');
    }
};