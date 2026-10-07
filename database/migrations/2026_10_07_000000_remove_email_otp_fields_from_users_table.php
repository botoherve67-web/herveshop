<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'email_verification_otp',
        'email_verification_otp_expires_at',
        'email_verification_otp_sent_at',
        'email_verification_otp_attempts',
        'email_verification_otp_locked_until',
    ];

    public function up(): void
    {
        $existingColumns = array_values(array_filter(
            $this->columns,
            fn (string $column): bool => Schema::hasColumn('users', $column)
        ));

        if ($existingColumns !== []) {
            Schema::table('users', function (Blueprint $table) use ($existingColumns): void {
                $table->dropColumn($existingColumns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('email_verification_otp')->nullable();
            $table->timestamp('email_verification_otp_expires_at')->nullable();
            $table->timestamp('email_verification_otp_sent_at')->nullable();
            $table->unsignedTinyInteger('email_verification_otp_attempts')->default(0);
            $table->timestamp('email_verification_otp_locked_until')->nullable();
        });
    }
};
