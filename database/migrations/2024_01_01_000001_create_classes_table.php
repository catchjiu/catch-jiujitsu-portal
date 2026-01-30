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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., Morning Fundamentals, Advanced No-Gi
            $table->enum('type', ['Gi', 'No-Gi', 'Open Mat', 'Fundamentals']);
            $table->dateTime('start_time');
            $table->integer('duration_minutes')->default(60);
            $table->string('instructor_name');
            $table->integer('capacity')->default(20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
