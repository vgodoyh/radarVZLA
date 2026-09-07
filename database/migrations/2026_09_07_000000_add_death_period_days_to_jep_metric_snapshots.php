<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->unsignedSmallInteger('deaths_period_start_day')->nullable()->after('deaths_period_start_month');
            $table->unsignedSmallInteger('deaths_period_end_day')->nullable()->after('deaths_period_end_month');
        });
    }

    public function down(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->dropColumn(['deaths_period_start_day', 'deaths_period_end_day']);
        });
    }
};
