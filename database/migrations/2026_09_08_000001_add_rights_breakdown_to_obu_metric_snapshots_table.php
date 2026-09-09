<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obu_metric_snapshots', function (Blueprint $table): void {
            $table->json('rights_breakdown')->nullable()->after('complaints_five_years');
        });
    }

    public function down(): void
    {
        Schema::table('obu_metric_snapshots', function (Blueprint $table): void {
            $table->dropColumn('rights_breakdown');
        });
    }
};
