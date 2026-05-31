<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->tinyInteger('rating')->unsigned();
            $table->string('title')->nullable();
            $table->text('comment')->nullable();

            $table->jsonb('pros')->nullable(); // Достоинства
            $table->jsonb('cons')->nullable(); // Недостатки

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['product_id', 'rating']);
            $table->index('user_id');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
