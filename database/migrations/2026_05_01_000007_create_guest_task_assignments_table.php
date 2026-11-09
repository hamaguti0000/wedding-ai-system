<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wedding_task_id')->constrained()->cascadeOnDelete();
            $table->string('custom_time', 50)->nullable();
            $table->text('custom_note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'wedding_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_task_assignments');
    }
};
