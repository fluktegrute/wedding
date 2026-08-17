<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->index(['is_featured', 'created_at']);
            $table->index(['is_synced_to_gdrive', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'created_at']);
            $table->dropIndex(['is_synced_to_gdrive', 'created_at']);
            $table->dropColumn('thumbnail_path');
        });
    }
};
