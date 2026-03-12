<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('badge_asjjf_legal')->default(false)->after('preorder_weeks');
            $table->boolean('badge_new')->default(false)->after('badge_asjjf_legal');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['badge_asjjf_legal', 'badge_new']);
        });
    }
};
