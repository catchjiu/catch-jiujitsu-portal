<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Normalize invalid dob (0000-00-00) first so ALTER TABLE does not fail in strict mode.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'dob')) {
            DB::statement("UPDATE users SET dob = NULL WHERE dob = '0000-00-00' OR dob < '1900-01-01'");
        }
        // For MySQL, we need to modify the enum to include new values
        DB::statement("ALTER TABLE users MODIFY COLUMN `rank` ENUM('White', 'Grey', 'Yellow', 'Orange', 'Green', 'Blue', 'Purple', 'Brown', 'Black') DEFAULT 'White'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN `rank` ENUM('White', 'Blue', 'Purple', 'Brown', 'Black') DEFAULT 'White'");
    }
};
