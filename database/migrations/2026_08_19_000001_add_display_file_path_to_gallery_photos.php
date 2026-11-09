<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            if (! Schema::hasColumn('gallery_photos', 'display_file_path')) {
                $table->string('display_file_path')->nullable()->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            if (Schema::hasColumn('gallery_photos', 'display_file_path')) {
                $table->dropColumn('display_file_path');
            }
        });
    }
};
