<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obu_bimonthly_alerts', function (Blueprint $table) {
            $table->text('url')->nullable()->after('excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('obu_bimonthly_alerts', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};
