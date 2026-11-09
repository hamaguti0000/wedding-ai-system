<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_type', 20)->default('initial')->after('role');
            $table->string('avatar_emoji', 20)->nullable()->after('avatar_type');
            $table->string('avatar_image_path')->nullable()->after('avatar_emoji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_type', 'avatar_emoji', 'avatar_image_path']);
        });
    }
};
