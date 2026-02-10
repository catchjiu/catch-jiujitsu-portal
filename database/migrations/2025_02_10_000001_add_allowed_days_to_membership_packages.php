<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restricts which days a package can book: null/all = any day, weekdays = Mon–Fri, weekends = Sat–Sun.
     */
    public function up(): void
    {
        Schema::table('membership_packages', function (Blueprint $table) {
            $table->string('allowed_days', 20)->nullable()->after('age_group')->comment('all, weekdays, weekends');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_packages', function (Blueprint $table) {
            $table->dropColumn('allowed_days');
        });
    }
};
