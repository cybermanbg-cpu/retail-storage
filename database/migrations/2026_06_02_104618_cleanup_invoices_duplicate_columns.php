<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Премахваме total_vat, защото използваме vat
            if (Schema::hasColumn('invoices', 'total_vat')) {
                $table->dropColumn('total_vat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total_vat', 12, 2)->default(0);
        });
    }
};