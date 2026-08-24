<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zip_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('status', 24)->default('ready');
            $table->string('gallery_category', 32)->default('reception');
            $table->unsignedInteger('total_entries')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_import_batches');
    }
};
