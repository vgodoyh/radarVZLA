<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jep_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations', 'id')->cascadeOnDelete();
            $table->unsignedInteger('total_political_prisoners');
            $table->unsignedInteger('women');
            $table->unsignedInteger('seriously_ill');
            $table->unsignedInteger('foreign_or_dual_nationality');
            $table->unsignedInteger('releases');
            $table->unsignedSmallInteger('releases_period_start_month')->nullable();
            $table->unsignedSmallInteger('releases_period_start_day')->nullable();
            $table->unsignedSmallInteger('releases_period_start_year')->nullable();
            $table->unsignedSmallInteger('releases_period_end_month')->nullable();
            $table->unsignedSmallInteger('releases_period_end_day')->nullable();
            $table->unsignedSmallInteger('releases_period_end_year')->nullable();
            $table->unsignedInteger('active_retired_officials');
            $table->unsignedInteger('new_detentions');
            $table->unsignedInteger('missing_location');
            $table->unsignedInteger('deaths_in_custody');
            $table->unsignedSmallInteger('deaths_period_start_month')->nullable();
            $table->unsignedSmallInteger('deaths_period_start_year')->nullable();
            $table->unsignedSmallInteger('deaths_period_end_month')->nullable();
            $table->unsignedSmallInteger('deaths_period_end_year')->nullable();
            $table->date('data_date')->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();
            $table->index(['organization_id', 'valid_until'], 'jep_snap_org_current_idx');
        });

        Schema::create('jep_vulnerable_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('jep_metric_snapshots', 'id')->cascadeOnDelete();
            $table->string('group_key', 80);
            $table->string('label');
            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['snapshot_id', 'sort_order'], 'jep_groups_snapshot_sort_idx');
        });

        Schema::create('jep_detention_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('jep_metric_snapshots', 'id')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['snapshot_id', 'sort_order'], 'jep_centers_snapshot_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jep_detention_centers');
        Schema::dropIfExists('jep_vulnerable_groups');
        Schema::dropIfExists('jep_metric_snapshots');
    }
};
