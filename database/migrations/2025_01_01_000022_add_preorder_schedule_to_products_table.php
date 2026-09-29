<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->date('date_cloture_precommande')->nullable()->after('acompte_pourcent');
            $table->date('date_expedition_prevue')->nullable()->after('date_cloture_precommande');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['date_cloture_precommande', 'date_expedition_prevue']);
        });
    }
};
