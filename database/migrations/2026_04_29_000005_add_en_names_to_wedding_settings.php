<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('groom_name_en', 100)->nullable()->after('groom_name');
            $table->string('bride_name_en', 100)->nullable()->after('bride_name');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['groom_name_en', 'bride_name_en']);
        });
    }
};
