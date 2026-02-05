<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safe when age_group column already exists on classes or users.
     */
    public function up(): void
    {
        if (Schema::hasTable('classes') && ! Schema::hasColumn('classes', 'age_group')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->enum('age_group', ['Kids', 'Adults', 'All'])->default('Adults')->after('type');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'age_group')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('age_group', ['Kids', 'Adults'])->default('Adults')->after('gender');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'age_group')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('age_group');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'age_group')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('age_group');
            });
        }
    }
};
