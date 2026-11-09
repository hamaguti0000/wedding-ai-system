<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('furigana_sei')->nullable();
            $table->string('furigana_mei')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('participation', ['attending', 'declining', 'pending'])->default('pending');
            $table->unsignedTinyInteger('children_count')->default(0);
            $table->string('dietary_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_profiles');
    }
};
