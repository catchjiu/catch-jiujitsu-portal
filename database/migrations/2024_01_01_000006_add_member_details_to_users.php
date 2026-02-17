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
        $afterName = Schema::hasColumn('users', 'name') ? 'name' : 'first_name';

        Schema::table('users', function (Blueprint $table) use ($afterName) {
            if (!Schema::hasColumn('users', 'chinese_name')) {
                $table->string('chinese_name')->nullable()->after($afterName);
            }
            if (!Schema::hasColumn('users', 'belt_color')) {
                $table->string('belt_color')->nullable()->after('rank');
            }
            if (!Schema::hasColumn('users', 'line_id')) {
                $table->string('line_id')->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('line_id');
            }
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable()->after('gender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chinese_name', 'belt_color', 'line_id', 'gender', 'dob']);
        });
    }
};
