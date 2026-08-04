<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('x_username')->nullable();
            $table->string('website_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('x_logo_path')->nullable();
            $table->string('color', 16)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('source', 40);
            $table->string('external_id')->nullable();
            $table->string('category')->nullable();
            $table->text('title')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('url');
            $table->text('image_url')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'source', 'external_id']);
            $table->index(['organization_id', 'published_at']);
        });

        Schema::create('indicator_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->string('label_es');
            $table->string('label_en');
            $table->string('unit')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('indicator_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_definition_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->decimal('value', 16, 2);
            $table->decimal('change_percentage', 8, 2)->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['indicator_definition_id', 'period_start']);
        });

        Schema::create('dashboard_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('running');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_sync_runs');
        Schema::dropIfExists('indicator_values');
        Schema::dropIfExists('indicator_definitions');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('organizations');
    }
};
