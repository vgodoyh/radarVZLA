<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_page_views')) {
            DB::table('analytics_page_views')
                ->where('organization', 'acceso_justicia')
                ->where('page', 'organization')
                ->update(['page' => 'organizaciones/acceso-justicia']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('analytics_page_views')) {
            DB::table('analytics_page_views')
                ->where('organization', 'acceso_justicia')
                ->where('page', 'organizaciones/acceso-justicia')
                ->update(['page' => 'organization']);
        }
    }
};
