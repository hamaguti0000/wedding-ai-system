<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_settings', function (Blueprint $table) {
            $table->id();
            $table->string('groom_name');
            $table->string('bride_name');
            $table->date('ceremony_date');
            $table->time('ceremony_time');
            $table->time('reception_time');
            $table->string('venue_name');
            $table->string('venue_address');
            $table->string('venue_url')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_settings');
    }
};
