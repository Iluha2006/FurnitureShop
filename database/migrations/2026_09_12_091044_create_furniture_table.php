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
        Schema::create('furniture', function (Blueprint $table) {
            $table->id('furniture_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(0);
            $table->foreignId('manufacturer_id')
                ->nullable()
                ->constrained('furniture_manufacturers', 'manufacturer_id')
                ->nullOnDelete();
            $table->foreignId('category_id')
                ->constrained('furniture_categories', 'category_id')
                ->cascadeOnDelete();
            $table->foreignId('specifications_id')
                ->nullable()
                ->constrained('furniture_specifications')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furniture');
    }
};
