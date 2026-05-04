<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('checkin_token', 80)->nullable()->unique()->after('responded_at');
            $table->timestamp('checked_in_at')->nullable()->after('checkin_token');
            $table->foreignId('checked_in_by_user_id')
                ->nullable()
                ->after('checked_in_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by_user_id');
            $table->dropColumn(['checkin_token', 'checked_in_at']);
        });
    }
};
