<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('program_items', function (Blueprint $table) {
            $table->id();
            $table->string('start_time', 10)->nullable();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('fa-circle');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('program_items'); }
};
