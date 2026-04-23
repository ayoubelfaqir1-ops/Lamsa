<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->decimal('starting_price', 10, 2);
            $table->decimal('reserve_price', 10, 2)->nullable();
            $table->decimal('current_price', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_published')->default(false);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamps();

            $table->index('store_id');
            $table->index('artisan_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('status');
            $table->index('is_published');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
