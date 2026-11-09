<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_images', function (Blueprint $table) {
            $table->id();
            $table->string('location', 50);
            $table->string('image_path', 500);
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['location', 'is_active', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_images');
    }
};
