<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('chinese_name')->nullable()->after('name');
            $table->string('belt_color')->nullable()->after('rank');
            $table->string('line_id')->nullable()->after('avatar_url');
            $table->string('gender')->nullable()->after('line_id');
            $table->date('dob')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chinese_name', 'belt_color', 'line_id', 'gender', 'dob']);
        });
    }
};
