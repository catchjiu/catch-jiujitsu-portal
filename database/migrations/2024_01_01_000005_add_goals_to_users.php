<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('monthly_class_goal')->default(12)->after('mat_hours');
            $table->integer('monthly_hours_goal')->default(15)->after('monthly_class_goal');
            $table->boolean('reminders_enabled')->default(true)->after('monthly_hours_goal');
            $table->boolean('public_profile')->default(false)->after('reminders_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_class_goal', 'monthly_hours_goal', 'reminders_enabled', 'public_profile']);
        });
    }
};
