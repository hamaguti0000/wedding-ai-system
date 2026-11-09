<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            if (! Schema::hasColumn('gallery_photos', 'gallery_category')) {
                $table->string('gallery_category', 32)->default('other')->after('caption');
            }
            if (! Schema::hasColumn('gallery_photos', 'photo_source')) {
                $table->string('photo_source', 32)->default('admin')->after('gallery_category');
            }
            $table->index('gallery_category');
            $table->index('photo_source');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            if (Schema::hasColumn('gallery_photos', 'gallery_category')) {
                $table->dropIndex(['gallery_category']);
            }
            if (Schema::hasColumn('gallery_photos', 'photo_source')) {
                $table->dropIndex(['photo_source']);
            }
            $table->dropColumn(array_values(array_filter([
                Schema::hasColumn('gallery_photos', 'gallery_category') ? 'gallery_category' : null,
                Schema::hasColumn('gallery_photos', 'photo_source') ? 'photo_source' : null,
            ])));
        });
    }
};
