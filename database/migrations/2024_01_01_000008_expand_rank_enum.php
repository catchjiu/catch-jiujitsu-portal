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
        $driver = DB::getDriverName();

        if ($driver === 'mysql' && Schema::hasColumn('users', 'dob')) {
            DB::statement("UPDATE users SET dob = NULL WHERE dob = '0000-00-00' OR dob < '1900-01-01'");
        }

        // MySQL uses native ENUM and needs explicit ALTER; PostgreSQL / SQLite store as text+check
        // in this project, so we skip this driver-specific statement there.
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN `rank` ENUM('White', 'Grey', 'Yellow', 'Orange', 'Green', 'Blue', 'Purple', 'Brown', 'Black') DEFAULT 'White'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN `rank` ENUM('White', 'Blue', 'Purple', 'Brown', 'Black') DEFAULT 'White'");
        }
    }
};
