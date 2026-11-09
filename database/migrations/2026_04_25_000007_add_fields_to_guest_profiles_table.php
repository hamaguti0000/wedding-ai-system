<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('postal_code', 8)->nullable()->after('phone');
            $table->unsignedTinyInteger('attending_count')->default(1)->after('children_count');
            $table->text('allergy_notes')->nullable()->after('dietary_notes');
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropColumn(['postal_code', 'attending_count', 'allergy_notes']);
        });
    }
};
