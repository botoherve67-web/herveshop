<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedInteger('report_count')->default(0)->after('is_approved');
            $table->text('report_reason')->nullable()->after('report_count');
            $table->timestamp('reported_at')->nullable()->after('report_reason');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['report_count', 'report_reason', 'reported_at']);
        });
    }
};