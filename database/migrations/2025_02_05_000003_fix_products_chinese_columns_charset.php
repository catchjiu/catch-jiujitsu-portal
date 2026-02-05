<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix product_name_zh and product_desc_zh to use utf8mb4 so Chinese characters save correctly.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }
        DB::statement('ALTER TABLE products MODIFY product_name_zh VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        DB::statement('ALTER TABLE products MODIFY product_desc_zh TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
    }

    /**
     * Reverse the migrations (revert to default table charset; may break existing Chinese data).
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }
        DB::statement('ALTER TABLE products MODIFY product_name_zh VARCHAR(255) NULL');
        DB::statement('ALTER TABLE products MODIFY product_desc_zh TEXT NULL');
    }
};
