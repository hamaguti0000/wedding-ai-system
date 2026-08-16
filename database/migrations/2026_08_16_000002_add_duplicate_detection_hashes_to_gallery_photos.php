<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            // 完全一致（同一ファイル）の検出用
            $table->string('file_hash', 64)->nullable()->after('is_guest_upload');
            // 見た目の類似判定（多少の再圧縮・リサイズを許容）の検出用
            $table->string('phash', 16)->nullable()->after('file_hash');
            $table->index('file_hash');
            $table->index('phash');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropIndex(['file_hash']);
            $table->dropIndex(['phash']);
            $table->dropColumn(['file_hash', 'phash']);
        });
    }
};
