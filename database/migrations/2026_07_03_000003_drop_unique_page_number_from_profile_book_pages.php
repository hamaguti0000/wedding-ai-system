<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_book_pages', function (Blueprint $table) {
            // 並び替え(moveUp/moveDown)で2件のpage_numberを入れ替える際、
            // 片方を先に更新した瞬間に一時的な重複が発生するため unique 制約を外す。
            $table->dropUnique(['page_number']);
        });
    }

    public function down(): void
    {
        Schema::table('profile_book_pages', function (Blueprint $table) {
            $table->unique('page_number');
        });
    }
};
