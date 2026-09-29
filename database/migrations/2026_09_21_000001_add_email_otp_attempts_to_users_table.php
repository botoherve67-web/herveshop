<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('email_verification_otp_attempts')->default(0)->after('email_verification_otp_sent_at');
            $table->timestamp('email_verification_otp_locked_until')->nullable()->after('email_verification_otp_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verification_otp_attempts', 'email_verification_otp_locked_until']);
        });
    }
};
