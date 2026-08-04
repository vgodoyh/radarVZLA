<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'last_synced_at')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable()->after('active');
        });

        if (Schema::hasTable('dashboard_sync_runs')) {
            $lastCompletedSync = DB::table('dashboard_sync_runs')
                ->where('status', 'completed')
                ->whereNotNull('finished_at')
                ->latest('finished_at')
                ->value('finished_at');

            if ($lastCompletedSync) {
                DB::table('organizations')
                    ->where('slug', 'acceso-justicia')
                    ->update(['last_synced_at' => $lastCompletedSync]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations') || ! Schema::hasColumn('organizations', 'last_synced_at')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('last_synced_at');
        });
    }
};
