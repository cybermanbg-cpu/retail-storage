<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->default(0)->change();
            $table->decimal('reserved_quantity', 12, 3)->default(0)->change();
            $table->decimal('min_quantity', 12, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
            $table->integer('reserved_quantity')->default(0)->change();
            $table->integer('min_quantity')->default(0)->change();
        });
    }
};