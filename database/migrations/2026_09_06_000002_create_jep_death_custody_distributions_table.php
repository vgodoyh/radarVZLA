<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jep_death_custody_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('snapshot_id');
            $table->string('category_key', 80);
            $table->string('label');
            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('snapshot_id', 'jep_death_snapshot_fk')
                ->references('id')
                ->on('jep_metric_snapshots')
                ->cascadeOnDelete();
            $table->index(['snapshot_id', 'sort_order'], 'jep_death_snapshot_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jep_death_custody_distributions');
    }
};
