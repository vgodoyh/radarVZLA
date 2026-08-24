<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_content_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('organization', 80);
            $table->string('content_type', 80);
            $table->unsignedBigInteger('content_id');
            $table->string('source', 40);
            $table->string('session_id', 64)->nullable();
            $table->timestamps();

            $table->index(['organization', 'content_type', 'content_id', 'source'], 'analytics_clicks_org_content_source_idx');
            $table->index(['organization', 'created_at'], 'analytics_clicks_org_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_content_clicks');
    }
};
