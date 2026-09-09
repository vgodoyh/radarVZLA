<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obu_metric_snapshots', function (Blueprint $table): void {
            $table->unsignedInteger('complaints_five_years')->nullable()->after('complaints');
        });
    }

    public function down(): void
    {
        Schema::table('obu_metric_snapshots', function (Blueprint $table): void {
            $table->dropColumn('complaints_five_years');
        });
    }
};
