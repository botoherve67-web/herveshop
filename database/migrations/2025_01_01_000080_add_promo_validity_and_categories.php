<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->date('commence_le')->nullable()->after('usage_actuel');
        });

        Schema::create('category_promo_code', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['promo_code_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_promo_code');

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn('commence_le');
        });
    }
};