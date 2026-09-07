<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->string('featured_indicator_title')->nullable()->after('monthly_alert_x_url');
            $table->text('featured_indicator_text')->nullable()->after('featured_indicator_title');
            $table->text('featured_indicator_instagram_url')->nullable()->after('featured_indicator_text');
            $table->text('featured_indicator_x_url')->nullable()->after('featured_indicator_instagram_url');
            $table->string('featured_indicator_image_path')->nullable()->after('featured_indicator_x_url');
        });
    }

    public function down(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'featured_indicator_title',
                'featured_indicator_text',
                'featured_indicator_instagram_url',
                'featured_indicator_x_url',
                'featured_indicator_image_path',
            ]);
        });
    }
};
