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
        Schema::create('furniture_specifications', function (Blueprint $table) {
            $table->id();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->string('folding_type')->nullable();
            $table->string('insert_type')->nullable();
            $table->string('materials')->nullable();
            $table->string('surface')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('package_volume_m3', 8, 3)->nullable();
            $table->string('warranty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furniture_specifications');
    }
};
