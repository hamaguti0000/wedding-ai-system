<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photo_taggings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['gallery_photo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photo_taggings');
    }
};
