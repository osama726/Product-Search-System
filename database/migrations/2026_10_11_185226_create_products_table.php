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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->unsigned();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Add full-text index for searching
            $table->fullText(['name', 'description']);

            // Add indexes for filtering and sorting
            $table->index(['category_id', 'is_available', 'price']);
            $table->index(['category_id', 'is_available', 'created_at']);
            $table->index(['is_available', 'price']);
            $table->index(['is_available', 'created_at']);
            $table->index('price');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
