<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('last_device_type')->nullable()->after('last_activity_at');
            $table->string('last_platform')->nullable()->after('last_device_type');
            $table->string('last_browser')->nullable()->after('last_platform');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['last_device_type', 'last_platform', 'last_browser']);
        });
    }
};
