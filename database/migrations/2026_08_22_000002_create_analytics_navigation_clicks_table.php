<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_navigation_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('organization', 80);
            $table->string('target', 160);
            $table->string('source', 40);
            $table->string('session_id', 64)->nullable();
            $table->timestamps();

            $table->index(['organization', 'target', 'source'], 'analytics_navigation_org_target_source_idx');
            $table->index(['organization', 'created_at'], 'analytics_navigation_org_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_navigation_clicks');
    }
};
