<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'is_preorder')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_preorder')->default(false)->after('image_url');
            });
        }

        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'is_preorder')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->boolean('is_preorder')->default(false)->after('unit_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'is_preorder')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_preorder');
            });
        }
        if (Schema::hasColumn('order_items', 'is_preorder')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('is_preorder');
            });
        }
    }
};
