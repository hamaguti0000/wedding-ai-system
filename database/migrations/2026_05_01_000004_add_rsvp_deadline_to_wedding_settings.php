<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->date('rsvp_deadline')->nullable()->after('is_seating_public');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn('rsvp_deadline');
        });
    }
};
