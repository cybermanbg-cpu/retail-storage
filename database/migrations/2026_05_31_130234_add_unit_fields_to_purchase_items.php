<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
    $table->string('unit_of_measure_snapshot')->nullable()->after('final_unit_cost');
    $table->integer('decimal_places_snapshot')->default(0)->after('unit_of_measure_snapshot');
});
    }
};
