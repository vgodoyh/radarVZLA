<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->string('monthly_alert_title')->nullable()->after('detentions_methodology_note');
            $table->text('monthly_alert_excerpt')->nullable()->after('monthly_alert_title');
            $table->text('monthly_alert_x_url')->nullable()->after('monthly_alert_excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->dropColumn(['monthly_alert_title', 'monthly_alert_excerpt', 'monthly_alert_x_url']);
        });
    }
};
