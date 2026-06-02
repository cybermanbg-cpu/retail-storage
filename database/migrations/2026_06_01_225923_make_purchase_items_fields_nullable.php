<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Правете ги nullable или със default стойност
            $table->decimal('total_cost', 12, 4)->nullable()->default(0)->change();
            $table->decimal('final_unit_cost', 12, 4)->nullable()->default(0)->change();
            $table->decimal('delivery_cost_share', 12, 4)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('total_cost', 12, 4)->nullable(false)->change();
            $table->decimal('final_unit_cost', 12, 4)->nullable(false)->change();
            $table->decimal('delivery_cost_share', 12, 4)->nullable(false)->change();
        });
    }
};