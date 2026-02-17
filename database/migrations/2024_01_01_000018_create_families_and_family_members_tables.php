<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['parent', 'child'])->default('parent');
            $table->timestamps();
            $table->unique(['family_id', 'user_id']);
            $table->unique('user_id'); // A user can only belong to one family
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('families');
    }
};
