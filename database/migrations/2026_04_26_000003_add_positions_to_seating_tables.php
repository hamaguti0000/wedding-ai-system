<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seating_tables', function (Blueprint $table) {
            $table->unsignedSmallInteger('pos_x')->default(0)->after('display_order');
            $table->unsignedSmallInteger('pos_y')->default(0)->after('pos_x');
        });
    }

    public function down(): void
    {
        Schema::table('seating_tables', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y']);
        });
    }
};
