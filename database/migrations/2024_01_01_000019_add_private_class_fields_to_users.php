<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('accepting_private_classes')->default(false)->after('is_coach');
            $table->decimal('private_class_price', 10, 2)->nullable()->after('accepting_private_classes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accepting_private_classes', 'private_class_price']);
        });
    }
};
