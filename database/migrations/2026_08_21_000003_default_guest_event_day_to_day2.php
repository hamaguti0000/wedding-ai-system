<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('guest_profiles')
            ->whereNull('event_day')
            ->update(['event_day' => 'day2']);

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('event_day', 20)->nullable()->default('day2')->change();
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('event_day', 20)->nullable()->default(null)->change();
        });
    }
};
