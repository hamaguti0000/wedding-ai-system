<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password_change_required')->default(true)->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('password_change_required');
        });

        DB::table('users')
            ->where('role', 'admin')
            ->update([
                'password_change_required' => false,
                'password_changed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_change_required', 'password_changed_at']);
        });
    }
};
