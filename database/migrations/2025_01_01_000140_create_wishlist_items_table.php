<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('list_type', ['favorite', 'later'])->default('favorite');
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
            $table->index(['user_id', 'list_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};