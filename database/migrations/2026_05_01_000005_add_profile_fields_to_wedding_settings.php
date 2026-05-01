<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->string('groom_photo')->nullable();
            $table->text('groom_bio')->nullable();
            $table->string('bride_photo')->nullable();
            $table->text('bride_bio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wedding_settings', function (Blueprint $table) {
            $table->dropColumn(['groom_photo', 'groom_bio', 'bride_photo', 'bride_bio']);
        });
    }
};
