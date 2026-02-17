<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track when we last sent a "we miss you" re-engagement LINE message
     * so we don't send more than once per 7 days.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_reengagement_line_sent_at')->nullable()->after('classes_zero_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_reengagement_line_sent_at');
        });
    }
};
