<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
