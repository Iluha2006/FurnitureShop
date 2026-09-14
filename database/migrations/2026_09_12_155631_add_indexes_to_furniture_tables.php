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
        Schema::table('furniture', function (Blueprint $table) {
            $table->index(['manufacturer_id'], 'furniture_manufacturer_id_index');
            $table->index(['category_id'], 'furniture_category_id_index');
            $table->index(['specifications_id'], 'furniture_specifications_id_index');
            $table->index(['price'], 'furniture_price_index');
            $table->index(['quantity'], 'furniture_quantity_index');
        });

        Schema::table('furniture_images', function (Blueprint $table) {
            $table->index(['furniture_id'], 'furniture_images_furniture_id_index');
            $table->index(['is_main'], 'furniture_images_is_main_index');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id'], 'carts_user_id_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->index(['furniture_id'], 'cart_items_furniture_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id'], 'orders_user_id_index');
            $table->index(['status'], 'orders_status_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id'], 'order_items_order_id_index');
            $table->index(['furniture_id'], 'order_items_furniture_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('furniture', function (Blueprint $table) {
            $table->dropIndex('furniture_manufacturer_id_index');
            $table->dropIndex('furniture_category_id_index');
            $table->dropIndex('furniture_specifications_id_index');
            $table->dropIndex('furniture_price_index');
            $table->dropIndex('furniture_quantity_index');
        });

        Schema::table('furniture_images', function (Blueprint $table) {
            $table->dropIndex('furniture_images_furniture_id_index');
            $table->dropIndex('furniture_images_is_main_index');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_id_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_furniture_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_index');
            $table->dropIndex('orders_status_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_id_index');
            $table->dropIndex('order_items_furniture_id_index');
        });
    }
};
