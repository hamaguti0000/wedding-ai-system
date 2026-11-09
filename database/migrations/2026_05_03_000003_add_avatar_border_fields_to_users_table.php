<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_border_color', 20)->nullable()->after('avatar_bg_color');
            $table->unsignedTinyInteger('avatar_border_width')->default(3)->after('avatar_border_color');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_border_color', 'avatar_border_width']);
        });
    }
};
