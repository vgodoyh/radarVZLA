<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obu_monthly_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations', 'id', 'obu_notes_org_fk');
            $table->string('title');
            $table->text('excerpt');
            $table->string('image_path')->nullable();
            $table->date('publication_date');
            $table->boolean('is_published')->default(false);
            $table->text('url')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id', 'obu_notes_user_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'is_published', 'publication_date'], 'obu_notes_public_idx');
        });

        Schema::create('obu_bimonthly_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations', 'id', 'obu_alerts_org_fk');
            $table->string('title');
            $table->text('excerpt');
            $table->string('image_path')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->boolean('is_published')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users', 'id', 'obu_alerts_user_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'is_published', 'period_end'], 'obu_alerts_public_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obu_bimonthly_alerts');
        Schema::dropIfExists('obu_monthly_notes');
    }
};
