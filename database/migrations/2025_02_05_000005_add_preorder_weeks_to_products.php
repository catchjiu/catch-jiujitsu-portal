<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'preorder_weeks')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedTinyInteger('preorder_weeks')->nullable()->after('is_preorder');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'preorder_weeks')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('preorder_weeks');
            });
        }
    }
};
