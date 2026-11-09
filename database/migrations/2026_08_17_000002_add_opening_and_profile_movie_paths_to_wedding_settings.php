<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('opening_movie_path')->nullable()->after('hero_video_path');
            $table->string('profile_movie_path')->nullable()->after('opening_movie_path');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['opening_movie_path', 'profile_movie_path']);
        });
    }
};
