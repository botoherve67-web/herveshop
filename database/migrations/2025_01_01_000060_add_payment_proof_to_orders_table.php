<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('moyen_paiement');
            $table->string('preuve_paiement_path')->nullable()->after('transaction_id');
            $table->timestamp('preuve_paiement_envoyee_at')->nullable()->after('preuve_paiement_path');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'preuve_paiement_path',
                'preuve_paiement_envoyee_at',
            ]);
        });
    }
};