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
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->string('delivery_address', 500)->nullable()->after('customer_email');
            $table->text('comment')->nullable()->after('delivery_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'customer_email',
                'delivery_address',
                'comment',
            ]);
        });
    }
};
