<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
            // 'approved' = 管理者アップロード or 承認済み, 'pending' = 承認待ち, 'rejected' = 却下
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('approved')->after('uploaded_by_user_id');
            $table->boolean('is_guest_upload')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by_user_id']);
            $table->dropColumn(['uploaded_by_user_id', 'status', 'is_guest_upload']);
        });
    }
};
