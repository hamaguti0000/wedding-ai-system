<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seating_table_group_assignments')) {
            return;
        }

        Schema::create('seating_table_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seating_table_id')->constrained()->cascadeOnDelete();
            $table->string('guest_group_id');
            $table->timestamps();

            $table->unique('guest_group_id', 'seat_group_guest_unique');
            $table->index('seating_table_id', 'seat_group_table_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seating_table_group_assignments');
    }
};
