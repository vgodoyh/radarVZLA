<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obu_dataset_value_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('obu_dataset_value_id')->constrained('obu_dataset_values')->restrictOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('dataset_key', 80);
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('category', 120);
            $table->string('subgroup', 80)->nullable();
            $table->string('label', 255);
            $table->unsignedInteger('value');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'dataset_key', 'valid_until'], 'obu_dataset_versions_lookup_idx');
            $table->index(['obu_dataset_value_id', 'valid_from'], 'obu_dataset_versions_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obu_dataset_value_versions');
    }
};
