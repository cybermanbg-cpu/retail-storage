<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'average_cost')) {
                $table->decimal('average_cost', 12, 2)->default(0)->after('min_quantity');
            }
            if (!Schema::hasColumn('stocks', 'cost_layers')) {
                $table->json('cost_layers')->nullable()->after('average_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['average_cost', 'cost_layers']);
        });
    }
};