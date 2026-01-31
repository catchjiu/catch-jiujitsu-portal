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
            $table->foreignId('membership_package_id')->nullable()->after('age_group')->constrained('membership_packages')->nullOnDelete();
            $table->enum('membership_status', ['active', 'expired', 'pending', 'none'])->default('none')->after('membership_package_id');
            $table->date('membership_expires_at')->nullable()->after('membership_status');
            $table->integer('classes_remaining')->nullable()->after('membership_expires_at');
            
            $table->index('membership_status');
            $table->index('membership_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['membership_package_id']);
            $table->dropColumn(['membership_package_id', 'membership_status', 'membership_expires_at', 'classes_remaining']);
        });
    }
};
