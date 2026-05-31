<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_of_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // Брой, Килограм, Литри, Метри и т.н.
            $table->string('code')->unique(); // бр, кг, л, м
            $table->string('symbol')->nullable(); // бр, kg, L, m
            $table->integer('decimal_places')->default(0); // 0 за брой, 2 за кг
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['owner_id', 'code']);
        });
        
        // Добавяне на стандартни мерни единици за всички собственици (owner_id = NULL)
        DB::table('unit_of_measures')->insert([
            ['owner_id' => null, 'name' => 'Брой', 'code' => 'pcs', 'symbol' => 'бр.', 'decimal_places' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Килограм', 'code' => 'kg', 'symbol' => 'кг', 'decimal_places' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Литър', 'code' => 'l', 'symbol' => 'л', 'decimal_places' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Метър', 'code' => 'm', 'symbol' => 'м', 'decimal_places' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Квадратен метър', 'code' => 'sqm', 'symbol' => 'м²', 'decimal_places' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Час', 'code' => 'hour', 'symbol' => 'ч', 'decimal_places' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Ден', 'code' => 'day', 'symbol' => 'дн', 'decimal_places' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['owner_id' => null, 'name' => 'Комплект', 'code' => 'set', 'symbol' => 'компл.', 'decimal_places' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_of_measures');
    }
};