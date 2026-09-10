<?php

namespace Database\Seeders;

use App\Models\ObuDatasetValue;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class ObuDatasetSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'universidades')->value('id');
        $rows = [];
        $add = function (string $dataset, ?int $year, string $category, ?string $subgroup, string $label, int $value, ?float $percentage, int $order) use (&$rows, $organizationId): void {
            $rows[] = compact('organizationId', 'dataset', 'year', 'category', 'subgroup', 'label', 'value', 'percentage', 'order');
        };

        foreach ([['Salarios dignos', 68], ['Daños en infraestructura', 19], ['Providencias estudiantiles', 12]] as $i => [$label, $value]) {
            $add('documented_complaints', null, 'economic_social', $label, $label, $value, null, $i + 1);
        }
        foreach ([['Autonomía universitaria', 8], ['Libertad de expresión', 6], ['Participación en asuntos públicos', 14]] as $i => [$label, $value]) {
            $add('documented_complaints', null, 'civil_political', $label, $label, $value, null, $i + 1);
        }
        foreach ([['Paro', 29], ['Concentración', 20], ['Marcha', 14], ['Pancartazo', 7], ['Otro', 5]] as $i => [$label, $value]) {
            $add('protest_types', null, 'protest', $label, $label, $value, null, $i + 1);
        }
        foreach ([2020 => 53, 2021 => 74, 2022 => 78, 2023 => 103, 2024 => 37, 2025 => 34] as $year => $value) {
            $add('university_protests_by_year', $year, 'protests', null, 'Protestas universitarias', $value, null, $year - 2019);
        }

        foreach (range(2020, 2025) as $i => $year) {
            $add('historical_complaints', $year, 'economic_social', null, 'Derechos económicos y sociales', [219, 154, 208, 139, 151, 134][$i], null, 1);
            $add('historical_complaints', $year, 'civil_political', null, 'Derechos civiles y políticos', [30, 26, 36, 23, 44, 29][$i], null, 2);
        }

        foreach ([
            ['UCV', 'Central de Venezuela', 238, 20], ['UCLA', 'Centro Occidental Lisandro Alvarado', 131, 11],
            ['UPEL', 'Pedagógica Experimental Libertador', 120, 10], ['UNELLEZ', 'Nac Exp de Los Llanos Occidentales Ezequiel Zamora', 111, 9],
            ['UNERG', 'Nac Exp de los Llanos Centrales Rómulo Gallegos', 89, 7], ['UC', 'De Carabobo', 80, 7],
            ['UNEFM', 'Nac Exp Francisco de Miranda', 80, 7], ['UDO', 'De Oriente', 79, 7],
            ['ULA', 'De Los Andes', 61, 5], ['UNET', 'Nac Exp del Táchira', 43, 4],
        ] as $i => [$code, $label, $value, $percentage]) {
            $add('university_ranking', 2025, $code, null, $label, $value, $percentage, $i + 1);
        }

        $sourceLabels = ['Representante estudiantil', 'Autoridad universitaria', 'Representante del gremio docente', 'Representante de sindicatos administrativo y obrero', 'Universidad', 'Otros'];
        foreach ([2020 => [97, 58, 26, 17, 22, 30], 2021 => [66, 52, 31, 13, 6, 12], 2022 => [49, 45, 77, 23, 13, 37], 2023 => [51, 41, 34, 13, 3, 20], 2024 => [58, 40, 49, 15, 18, 15], 2025 => [40, 10, 57, 17, 31, 8]] as $year => $values) {
            foreach ($values as $i => $value) {
                $add('complaint_sources', $year, 'source', $sourceLabels[$i], $sourceLabels[$i], $value, null, $i + 1);
            }
        }

        $news = [2020 => [[243, 6, 0], [0, 0, 0], [0, 0, 0]], 2021 => [[172, 8, 0], [0, 0, 0], [0, 0, 0]], 2022 => [[235, 9, 0], [0, 0, 0], [0, 0, 0]], 2023 => [[152, 10, 0], [0, 0, 0], [0, 0, 0]], 2024 => [[182, 13, 0], [0, 0, 0], [0, 0, 0]], 2025 => [[158, 5, 0], [542, 327, 0], [119, 11, 0]]];
        $categories = ['DENUNCIA', 'ACTIVIDAD', 'INFORMACIÓN'];
        $subgroups = ['Experimentales y autónomas', 'Controladas', 'Todas'];
        foreach ($news as $year => $categoryRows) {
            foreach ($categoryRows as $categoryIndex => $values) {
                foreach ($values as $subgroupIndex => $value) {
                    $add('news_by_university_type', $year, $categories[$categoryIndex], $subgroups[$subgroupIndex], $subgroups[$subgroupIndex], $value, null, ($categoryIndex * 3) + $subgroupIndex + 1);
                }
            }
        }

        foreach ($rows as $row) {
            $dataset = ObuDatasetValue::query()->where('organization_id', $organizationId)->where('dataset_key', $row['dataset'])->where('period_year', $row['year'])->where('category', $row['category'])->where('label', $row['label'])->first() ?? new ObuDatasetValue;
            $dataset->fill(['organization_id' => $organizationId, 'dataset_key' => $row['dataset'], 'period_year' => $row['year'], 'category' => $row['category'], 'subgroup' => $row['subgroup'], 'label' => $row['label'], 'value' => $row['value'], 'percentage' => $row['percentage'], 'sort_order' => $row['order']])->save();
        }
    }
}
