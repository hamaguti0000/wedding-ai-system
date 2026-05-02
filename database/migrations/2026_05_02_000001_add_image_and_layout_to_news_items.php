<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->string('title', 100)->nullable()->after('tag');
            $table->string('image_path', 255)->nullable()->after('body');
            $table->string('layout', 20)->default('text')->after('image_path');
            $table->unsignedSmallInteger('sort_order')->default(0)->change();
        });

        // body の長さを 500 → 1000 に拡張
        Schema::table('news_items', function (Blueprint $table) {
            $table->string('body', 1000)->change();
        });
    }

    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn(['title', 'image_path', 'layout']);
            $table->string('body', 500)->change();
        });
    }
};
