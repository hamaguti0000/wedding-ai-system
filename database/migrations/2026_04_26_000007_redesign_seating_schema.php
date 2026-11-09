<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // seating_tables から capacity を削除（席数は seats の count で把握）
        Schema::table('seating_tables', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        // 既存の seat_assignments を削除（構造が変わるため再作成）
        Schema::dropIfExists('seat_assignments');

        // seats テーブル新規作成
        // pos_x/pos_y はテーブル内の相対座標（px）
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_table_id')
                ->constrained('seating_tables')
                ->cascadeOnDelete();
            $table->string('type', 30)->default('normal');
            // type: normal / groom / bride / vip / family / colleague / other
            // 文字列型にすることで将来的に新タイプをマイグレーション不要で追加可能
            $table->string('label', 20)->nullable(); // 席番号など（A1, B3 等）
            $table->unsignedSmallInteger('pos_x')->default(12);
            $table->unsignedSmallInteger('pos_y')->default(12);
            $table->timestamps();
        });

        // seat_assignments 再作成（席単位の割り当て）
        Schema::create('seat_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_id')
                ->unique()                      // 1席に1ゲストのみ
                ->constrained('seats')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->unique()                      // 1ゲストは1席のみ
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_assignments');
        Schema::dropIfExists('seats');

        Schema::table('seating_tables', function (Blueprint $table) {
            $table->unsignedTinyInteger('capacity')->default(8)->after('display_order');
        });

        Schema::create('seat_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_table_id')->constrained('seating_tables')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
