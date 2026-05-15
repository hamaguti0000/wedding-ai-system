<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('hero_type', 20)->default('slideshow')->after('shuttle_bus_map_url');
            $table->string('hero_video_path', 500)->nullable()->after('hero_type');
            $table->unsignedInteger('hero_interval')->default(5000)->after('hero_video_path');
            $table->json('image_display_modes')->nullable()->after('hero_interval');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_type', 'hero_video_path', 'hero_interval', 'image_display_modes']);
        });
    }
};
