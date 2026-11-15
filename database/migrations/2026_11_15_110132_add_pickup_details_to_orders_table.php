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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method', 20)->default('delivery')
                ->after('customer_email');
            $table->foreignUuid('pickup_point_id')->nullable()
                ->after('delivery_method')
                ->constrained('pickup_points')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pickup_point_id']);
            $table->dropColumn(['delivery_method', 'pickup_point_id']);
        });
    }
};
