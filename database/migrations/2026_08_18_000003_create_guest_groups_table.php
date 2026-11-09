<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('guest_groups')) {
            Schema::create('guest_groups', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name')->nullable();
                $table->string('guest_side', 20)->nullable();
                $table->string('relationship', 40)->nullable();
                $table->foreignId('primary_guest_id')->nullable()->constrained('guest_profiles')->nullOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('guest_group_members')) {
            Schema::create('guest_group_members', function (Blueprint $table) {
                $table->id();
                $table->string('guest_group_id');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['guest_group_id', 'user_id'], 'guest_group_members_unique');
                $table->unique('user_id', 'guest_group_members_user_unique');
                $table->index('guest_group_id', 'guest_group_members_group_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_group_members');
        Schema::dropIfExists('guest_groups');
    }
};
