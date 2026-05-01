<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_program_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_task_id')->constrained()->cascadeOnDelete();
            $table->string('start_time', 20)->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_program_items');
    }
};
