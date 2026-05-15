<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('shuttle_bus_map_url', 500)->nullable()->after('shuttle_bus_note');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn('shuttle_bus_map_url');
        });
    }
};
