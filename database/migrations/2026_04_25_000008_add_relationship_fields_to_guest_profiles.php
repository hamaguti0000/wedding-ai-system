<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->enum('guest_side', ['groom', 'bride'])->nullable()->after('user_id');
            $table->enum('relationship', ['friend', 'family', 'colleague', 'other'])->nullable()->after('guest_side');
            $table->string('relationship_detail', 100)->nullable()->after('relationship');
            $table->boolean('has_allergy')->default(false)->after('children_count');
        });
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropColumn(['guest_side', 'relationship', 'relationship_detail', 'has_allergy']);
        });
    }
};
