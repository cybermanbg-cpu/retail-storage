<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('last_purchase_cost', 12, 4)->nullable()->after('average_cost');
            $table->timestamp('last_purchase_date')->nullable()->after('last_purchase_cost');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['last_purchase_cost', 'last_purchase_date']);
        });
    }
};