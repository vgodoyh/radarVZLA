<section class="jep-section jep-trends-resources" aria-labelledby="jep-trends-resources-title">
    @php
        $featuredWomenValue = data_get(
            collect(config('dashboard.stats', []))->firstWhere('key', 'women'),
            'value'
        );

        // DATOS PROVISIONALES PARA VISUALIZACIÓN.
        // Reemplazar Ene-Abr y Jun-Jul cuando se disponga de las cifras oficiales.
        // Mayo 2026 usa el único dato oficial disponible desde config/dashboard.php.
        $womenDetentionTrend = [
            'labels' => __('dashboard.jep_page.trends.months'),
            'values' => [198, 207, 216, 225, (int) $featuredWomenValue, 242, 251],
        ];
    @endphp

    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-trends-resources-title">{{ __('dashboard.jep_page.trends.combined_title') }}</h2>
    </header>

    <div class="jep-trends-resources__layout">
        <div class="jep-trends">
            <div class="jep-trends__chart">
                <canvas
                    id="jepWomenDetentionChart"
                    data-labels='@json($womenDetentionTrend['labels'])'
                    data-values='@json($womenDetentionTrend['values'])'
                    data-dataset-label="{{ __('dashboard.jep_page.trends.dataset_label') }}"
                    aria-label="{{ __('dashboard.jep_page.trends.chart_label') }}"
                ></canvas>
            </div>
            <p class="jep-trends__note">{{ __('dashboard.jep_page.trends.note') }}</p>
        </div>

        <aside class="jep-trends-resources__side" aria-label="{{ __('dashboard.jep_page.trends.conclusion_label') }}">
            <p class="jep-trends-resources__description">{{ __('dashboard.jep_page.trends.description') }}</p>
        </aside>
    </div>

    @include('dashboard.organizations.jep.resources')
</section>
