<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();

            // PostgreSQL JSONB для гибких характеристик
            $table->jsonb('specs')->nullable();
            // JSONB массив URL изображений
            $table->jsonb('images')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('brand')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Индексы для производительности
            $table->index('slug');
            $table->index('brand');
            $table->index('price');
            $table->index('is_new');
            $table->index('is_featured');
            $table->index('category_id');
            $table->index('created_at');

            // GIN индекс для быстрого поиска по JSONB (PostgreSQL)
            $table->rawIndex('specs', 'products_specs_gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
