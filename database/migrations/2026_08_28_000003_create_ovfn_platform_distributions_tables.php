<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ovfn_platform_distributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations', 'id', 'ovfn_platform_org_fk')
                ->cascadeOnDelete();
            $table->date('data_from_date');
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();
            $table->foreignId('user_id')->nullable()
                ->constrained('users', 'id', 'ovfn_platform_user_fk')
                ->nullOnDelete();
            $table->timestamps();
            $table->index(
                ['organization_id', 'valid_from', 'valid_until'],
                'ovfn_platform_current_idx'
            );
        });

        Schema::create('ovfn_platform_distribution_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('distribution_id')
                ->constrained('ovfn_platform_distributions', 'id', 'ovfn_platform_item_dist_fk')
                ->cascadeOnDelete();
            $table->string('platform', 40);
            $table->unsignedInteger('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(
                ['distribution_id', 'platform'],
                'ovfn_platform_item_platform_uq'
            );
            $table->index(
                ['distribution_id', 'sort_order'],
                'ovfn_platform_items_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ovfn_platform_distribution_items');
        Schema::dropIfExists('ovfn_platform_distributions');
    }
};
