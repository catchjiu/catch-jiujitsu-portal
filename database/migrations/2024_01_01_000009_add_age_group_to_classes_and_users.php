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
        // Add age_group to classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->enum('age_group', ['Kids', 'Adults', 'All'])->default('Adults')->after('type');
        });

        // Add age_group to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('age_group', ['Kids', 'Adults'])->default('Adults')->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('age_group');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('age_group');
        });
    }
};
