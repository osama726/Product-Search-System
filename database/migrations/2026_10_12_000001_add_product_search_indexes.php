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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'is_available', 'price', 'id'], 'products_category_available_price_id_index');
            $table->index(['is_available', 'category_id', 'created_at', 'id'], 'products_available_category_created_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_available_price_id_index');
            $table->dropIndex('products_available_category_created_id_index');
        });
    }
};
