<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer'])->nullable()->after('total_vat');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_method');
            $table->decimal('change_amount', 12, 2)->default(0)->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'amount_paid', 'change_amount']);
        });
    }
};