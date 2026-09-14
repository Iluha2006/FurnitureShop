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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('furniture_id')
                ->constrained('furniture', 'furniture_id')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_amount', 12, 2);
            $table->string('price_currency', 3)->default('RUB');
            $table->timestamps();

            $table->unique(['cart_id', 'furniture_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
