<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obu_dataset_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations', 'id', 'obu_dataset_org_fk');
            $table->string('dataset_key', 80);
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('category', 120);
            $table->string('subgroup', 80)->nullable();
            $table->string('label', 255);
            $table->unsignedInteger('value');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['organization_id', 'dataset_key', 'period_year', 'category', 'subgroup'],
                'obu_dataset_values_identity_uq'
            );
            $table->index(
                ['organization_id', 'dataset_key', 'period_year', 'sort_order'],
                'obu_dataset_values_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obu_dataset_values');
    }
};
