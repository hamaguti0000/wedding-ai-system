<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            // 席次表(is_seating_public)と同じ考え方: 準備ができるまでゲスト画面には出さない。
            $table->boolean('is_profile_book_public')->default(false)->after('is_seating_public');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn('is_profile_book_public');
        });
    }
};
