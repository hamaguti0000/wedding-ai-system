<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photo_group_taggings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_photo_id')->constrained()->cascadeOnDelete();
            $table->string('guest_group_id');
            $table->timestamps();

            $table->unique(['gallery_photo_id', 'guest_group_id']);
            $table->index('guest_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photo_group_taggings');
    }
};
