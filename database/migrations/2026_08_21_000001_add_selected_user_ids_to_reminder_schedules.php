<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table) {
            $table->json('selected_user_ids')->nullable()->after('target');
        });
    }

    public function down(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table) {
            $table->dropColumn('selected_user_ids');
        });
    }
};
