<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // 送信対象: 全員 / 出席予定者のみ / 未回答者のみ
            $table->enum('target', ['all', 'attending', 'not_responded'])->default('all');
            $table->string('subject');
            $table->text('message');
            // null = 即時送信用（まだ未送信なら pending のまま）
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'cancelled'])->default('pending');
            $table->unsignedInteger('sent_count')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_schedules');
    }
};
