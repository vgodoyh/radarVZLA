<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obu_monitoring_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations', 'id', 'obu_periods_org_fk');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('analyzed_information');
            $table->unsignedInteger('protests');
            $table->unsignedInteger('complaints');
            $table->date('data_date')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id', 'obu_periods_user_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'valid_until'], 'obu_periods_current_idx');
            $table->index(['organization_id', 'valid_from'], 'obu_periods_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obu_monitoring_periods');
    }
};
