<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('membership_expiry_reminder_sent_at')->nullable()->after('classes_remaining');
            $table->timestamp('classes_zero_reminder_sent_at')->nullable()->after('membership_expiry_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['membership_expiry_reminder_sent_at', 'classes_zero_reminder_sent_at']);
        });
    }
};
