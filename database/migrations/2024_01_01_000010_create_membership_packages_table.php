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
        Schema::create('membership_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('duration_type', ['days', 'weeks', 'months', 'years', 'classes'])->default('months');
            $table->integer('duration_value')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('age_group', ['Adults', 'Kids', 'All'])->default('All');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_packages');
    }
};
