<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->boolean('shuttle_bus_enabled')->default(false)->after('rsvp_deadline');
            $table->string('shuttle_bus_departure', 300)->nullable()->after('shuttle_bus_enabled');
            $table->string('shuttle_bus_times', 300)->nullable()->after('shuttle_bus_departure');
            $table->string('shuttle_bus_note', 500)->nullable()->after('shuttle_bus_times');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['shuttle_bus_enabled', 'shuttle_bus_departure', 'shuttle_bus_times', 'shuttle_bus_note']);
        });
    }
};
