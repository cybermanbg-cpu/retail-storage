<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('active_carts', function (Blueprint $table) {
            $table->foreignId('owner_id')->after('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
            $table->index(['owner_id', 'status']);
            $table->index(['cashier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('active_carts', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['cashier_id']);
            $table->dropColumn(['owner_id', 'cashier_id']);
        });
    }
};