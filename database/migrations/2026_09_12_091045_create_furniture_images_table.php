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
        Schema::create('furniture_images', function (Blueprint $table) {
            $table->id('images_id');
            $table->foreignId('furniture_id')
                ->constrained('furniture', 'furniture_id')
                ->cascadeOnDelete();
            $table->string('path_image');
            $table->boolean('is_main')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furniture_images');
    }
};
