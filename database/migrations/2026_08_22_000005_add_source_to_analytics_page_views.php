<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_page_views', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('page');
            $table->index(['organization', 'page', 'source', 'created_at'], 'analytics_page_views_origin_idx');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_page_views', function (Blueprint $table) {
            $table->dropIndex('analytics_page_views_origin_idx');
            $table->dropColumn('source');
        });
    }
};
