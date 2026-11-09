<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_book_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('page_number');
            $table->string('image_path');
            $table->timestamps();

            $table->unique('page_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_book_pages');
    }
};
