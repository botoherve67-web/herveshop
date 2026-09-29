<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['stock', 'precommande'])->default('stock');
            $table->enum('mode_livraison', ['domicile', 'point_retrait']);
            $table->string('zone_livraison')->nullable();
            $table->unsignedBigInteger('frais_livraison')->default(0);
            $table->string('adresse_livraison')->nullable();
            $table->string('point_retrait')->nullable();

            $table->unsignedBigInteger('sous_total');
            $table->unsignedBigInteger('reduction')->default(0);
            $table->string('code_promo')->nullable();
            $table->unsignedBigInteger('total');

            // Précommande : acompte / solde
            $table->unsignedBigInteger('montant_acompte')->default(0);
            $table->unsignedBigInteger('montant_solde')->default(0);
            $table->timestamp('solde_echeance_at')->nullable();

            $table->enum('moyen_paiement', ['flooz', 'tmoney'])->nullable();
            $table->enum('statut_paiement', ['en_attente', 'acompte_paye', 'paye', 'echec'])->default('en_attente');
            $table->enum('statut', [
                'en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee',
            ])->default('en_attente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
