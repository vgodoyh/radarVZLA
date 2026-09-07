<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obu_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('universities_monitored');
            $table->unsignedInteger('protests');
            $table->unsignedInteger('complaints');
            $table->date('data_date')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'valid_from']);
            $table->index(['organization_id', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obu_metric_snapshots');
    }
};
