<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gallery_photo_id')->nullable()->constrained('gallery_photos')->nullOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('display_file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 80)->nullable();
            $table->string('status', 24)->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['photographer_import_batch_id', 'status', 'sort_order'], 'photographer_import_items_batch_status_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_import_items');
    }
};
