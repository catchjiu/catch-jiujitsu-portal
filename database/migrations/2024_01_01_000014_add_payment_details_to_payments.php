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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank', 'linepay'])->nullable()->after('status');
            $table->date('payment_date')->nullable()->after('payment_method');
            $table->string('account_last_5', 5)->nullable()->after('payment_date');
            $table->index('payment_method', 'idx_payments_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_payment_method');
            $table->dropColumn(['payment_method', 'payment_date', 'account_last_5']);
        });
    }
};
