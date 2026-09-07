<?php

namespace Database\Seeders;

use App\Models\JepMetricSnapshot;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class JepMetricSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'jep')->first();
        if (! $organization) return;

        $methodologyNote = 'El registro de detenciones abarca aquellas ocurridas durante el período o aquellas ocurridas con anterioridad y han sido incorporadas a la base de datos recientemente. Estas últimas corresponden a casos reportados de forma continua y que son analizados rigurosamente para verificar el cumplimiento de los criterios de la organización para calificarlos como presos políticos.';
        $featuredIndicatorDefaults = [
            'featured_indicator_title' => __('dashboard.featured_title'),
            'featured_indicator_text' => __('dashboard.featured_analysis_jep'),
            'featured_indicator_instagram_url' => null,
            'featured_indicator_x_url' => null,
        ];
        $existing = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->first();
        if ($existing) {
            $updates = [];
            if (blank($existing->detentions_methodology_note)) $updates['detentions_methodology_note'] = $methodologyNote;
            foreach ($featuredIndicatorDefaults as $field => $default) {
                if (blank($existing->{$field}) && filled($default)) $updates[$field] = $default;
            }
            if ($updates) $existing->update($updates);
            return;
        }

        $snapshot = JepMetricSnapshot::create([
            'organization_id' => $organization->id,
            'total_political_prisoners' => 452, 'women' => 44, 'seriously_ill' => 38,
            'foreign_or_dual_nationality' => 44, 'releases' => 95,
            'releases_period_start_month' => 8, 'releases_period_start_day' => 14, 'releases_period_start_year' => 2026,
            'releases_period_end_month' => 8, 'releases_period_end_day' => 31, 'releases_period_end_year' => 2026,
            'active_retired_officials' => 198, 'new_detentions' => 8, 'missing_location' => 7, 'deaths_in_custody' => 27,
            'deaths_period_start_month' => 3, 'deaths_period_start_year' => 2015,
            'deaths_period_end_month' => 7, 'deaths_period_end_year' => 2025,
            'detentions_methodology_note' => $methodologyNote,
            ...$featuredIndicatorDefaults,
            'valid_from' => now(), 'user_id' => null,
        ]);

        foreach ([
            ['group_key' => 'sindicalistas', 'label' => 'Sindicalistas', 'value' => 2, 'sort_order' => 1],
            ['group_key' => 'organizaciones_politicas', 'label' => 'Organizaciones políticas', 'value' => 10, 'sort_order' => 2],
            ['group_key' => 'sociedad_civil', 'label' => 'Sociedad civil', 'value' => 222, 'sort_order' => 3],
        ] as $group) $snapshot->vulnerableGroups()->create($group);

        foreach ([
            ['name' => 'Centro Penitenciario Rodeo I', 'value' => 109, 'sort_order' => 1],
            ['name' => 'Centro Nacional de Procesados Militares (Ramo Verde)', 'value' => 76, 'sort_order' => 2],
            ['name' => 'Centro Penitenciario Fuerte Guaicaipuro', 'value' => 46, 'sort_order' => 3],
        ] as $center) $snapshot->detentionCenters()->create($center);

        foreach ([
            ['category_key' => 'home_arrest', 'label' => 'Arresto domiciliario', 'value' => 2, 'sort_order' => 1],
            ['category_key' => 'detention_centers', 'label' => 'En centros de reclusión', 'value' => 7, 'sort_order' => 2],
            ['category_key' => 'hospitals', 'label' => 'Hospitales', 'value' => 1, 'sort_order' => 3],
        ] as $distribution) $snapshot->deathCustodyDistribution()->create($distribution);
    }
}
