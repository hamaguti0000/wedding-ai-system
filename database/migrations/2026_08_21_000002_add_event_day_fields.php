<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('event_day', 20)->nullable()->after('guest_side')->index();
        });

        Schema::table('reminder_schedules', function (Blueprint $table) {
            $table->string('target_event_day', 20)->nullable()->after('selected_user_ids');
        });
    }

    public function down(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table) {
            $table->dropColumn('target_event_day');
        });

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropColumn('event_day');
        });
    }
};
