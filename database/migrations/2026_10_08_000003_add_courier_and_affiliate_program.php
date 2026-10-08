<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_type')->default('customer')->index();
            $table->string('affiliate_code', 16)->nullable()->unique();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('affiliate_partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('affiliate_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('eligible_amount');
            $table->unsignedBigInteger('commission_amount');
            $table->string('status')->default('credited');
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
            $table->index(['partner_id', 'status']);
        });

        Schema::create('affiliate_withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('payment_method', 20);
            $table->string('payment_number', 30);
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_withdrawal_requests');
        Schema::dropIfExists('affiliate_commissions');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('affiliate_partner_id');
            $table->dropConstrainedForeignId('courier_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['affiliate_code']);
            $table->dropIndex(['account_type']);
            $table->dropColumn(['account_type', 'affiliate_code']);
        });
    }
};
