<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->text('detentions_methodology_note')->nullable()->after('deaths_period_end_year');
        });

        DB::table('jep_metric_snapshots')
            ->whereNull('detentions_methodology_note')
            ->update([
                'detentions_methodology_note' => 'El registro de detenciones abarca aquellas ocurridas durante el período o aquellas ocurridas con anterioridad y han sido incorporadas a la base de datos recientemente. Estas últimas corresponden a casos reportados de forma continua y que son analizados rigurosamente para verificar el cumplimiento de los criterios de la organización para calificarlos como presos políticos.',
            ]);
    }

    public function down(): void
    {
        Schema::table('jep_metric_snapshots', function (Blueprint $table) {
            $table->dropColumn('detentions_methodology_note');
        });
    }
};
