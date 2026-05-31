<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->change();
            $table->decimal('unit_cost', 12, 4)->change();
            $table->decimal('total_cost', 12, 4)->change();
            $table->decimal('delivery_cost_share', 12, 4)->default(0)->change();
            $table->decimal('final_unit_cost', 12, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->decimal('unit_cost', 12, 2)->change();
            $table->decimal('total_cost', 12, 2)->change();
            $table->decimal('delivery_cost_share', 12, 2)->default(0)->change();
            $table->decimal('final_unit_cost', 12, 2)->change();
        });
    }
};