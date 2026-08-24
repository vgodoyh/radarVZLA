<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_page_views', function (Blueprint $table) {
            $table->id();
            $table->string('organization', 80);
            $table->string('page', 80);
            $table->string('session_id', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['organization', 'page', 'created_at'], 'analytics_page_views_org_page_date_idx');
            $table->index(['session_id', 'organization', 'page', 'created_at'], 'analytics_page_views_session_org_page_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_page_views');
    }
};
