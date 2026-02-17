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
            $table->enum('discount_type', ['none', 'gratis', 'fixed', 'percentage', 'half_price'])
                  ->default('none')
                  ->after('is_coach');
            $table->integer('discount_amount')->default(0)->after('discount_type');
            $table->index('discount_type', 'idx_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_discount_type');
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
