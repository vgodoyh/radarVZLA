<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_sync_runs', function (Blueprint $table) {
            $table->string('organization')->nullable()->after('id');
            $table->string('process')->nullable()->after('organization');
            $table->index(['organization', 'process', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_sync_runs', function (Blueprint $table) {
            $table->dropIndex(['organization', 'process', 'started_at']);
            $table->dropColumn(['organization', 'process']);
        });
    }
};
