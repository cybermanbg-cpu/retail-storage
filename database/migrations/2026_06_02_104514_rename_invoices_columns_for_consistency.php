<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Преименуване на колоните за съвместимост
            if (Schema::hasColumn('invoices', 'total_amount') && !Schema::hasColumn('invoices', 'total')) {
                $table->renameColumn('total_amount', 'total');
            }
            
            if (Schema::hasColumn('invoices', 'total_vat') && !Schema::hasColumn('invoices', 'vat')) {
                $table->renameColumn('total_vat', 'vat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'total')) {
                $table->renameColumn('total', 'total_amount');
            }
            
            if (Schema::hasColumn('invoices', 'vat')) {
                $table->renameColumn('vat', 'total_vat');
            }
        });
    }
};