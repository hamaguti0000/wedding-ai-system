<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('venue_nearest_station', 200)->nullable()->after('venue_url');
            $table->text('access_train')->nullable()->after('venue_nearest_station');
            $table->text('access_car')->nullable()->after('access_train');
            $table->text('access_parking')->nullable()->after('access_car');
            $table->text('venue_map_embed')->nullable()->after('access_parking');
        });
    }
    public function down(): void {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['venue_nearest_station','access_train','access_car','access_parking','venue_map_embed']);
        });
    }
};
