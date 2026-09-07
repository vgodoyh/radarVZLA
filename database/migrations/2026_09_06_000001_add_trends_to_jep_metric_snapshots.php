<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->decimal('total_political_prisoners_trend', 6, 2)->nullable()->after('total_political_prisoners');
            $table->decimal('women_trend', 6, 2)->nullable()->after('women');
            $table->decimal('seriously_ill_trend', 6, 2)->nullable()->after('seriously_ill');
            $table->decimal('foreign_or_dual_nationality_trend', 6, 2)->nullable()->after('foreign_or_dual_nationality');
            $table->decimal('releases_trend', 6, 2)->nullable()->after('releases');
        });
    }

    public function down(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'total_political_prisoners_trend', 'women_trend', 'seriously_ill_trend',
                'foreign_or_dual_nationality_trend', 'releases_trend',
            ]);
        });
    }
};
