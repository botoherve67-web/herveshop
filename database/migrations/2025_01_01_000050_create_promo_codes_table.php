<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['pourcentage', 'montant'])->default('pourcentage');
            $table->unsignedInteger('valeur'); // % ou FCFA
            $table->unsignedInteger('usage_max')->nullable();
            $table->unsignedInteger('usage_actuel')->default(0);
            $table->date('expire_le')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
