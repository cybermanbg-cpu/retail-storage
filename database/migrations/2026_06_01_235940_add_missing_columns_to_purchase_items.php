<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'total_cost')) {
                $table->decimal('total_cost', 12, 4)->default(0)->after('unit_cost');
            }
            if (!Schema::hasColumn('purchase_items', 'delivery_cost_share')) {
                $table->decimal('delivery_cost_share', 12, 4)->default(0)->after('total_cost');
            }
            if (!Schema::hasColumn('purchase_items', 'final_unit_cost')) {
                $table->decimal('final_unit_cost', 12, 4)->default(0)->after('delivery_cost_share');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['total_cost', 'delivery_cost_share', 'final_unit_cost']);
        });
    }
};