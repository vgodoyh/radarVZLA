@extends('layouts.public_v2')

@section('title', 'Observatorio de Universidades | Pulso Venezuela')

@section('content')
    @php
        $lastSyncAt = filled($lastSync ?? null)
            ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
            : null;
    @endphp

    <div class="organization-page organization-page--obu obu-page">
        @include('dashboard.partials.global-header', ['headerAccent' => '#fd8700'])

        @include('dashboard.organizations.partials.organization-hero', [
            'heroClass' => 'organization-v2-hero--obu',
            'accent' => '#fd8700',
            'accentRgb' => '253, 135, 0',
            'logo' => $organization['logo'],
            'category' => __('dashboard.university_title'),
            'title' => $organization['name'],
            'description' => __('dashboard.university_description'),
            'illustrationPartial' => 'dashboard.organizations.partials.illustrations.obu',
            'lastSyncAt' => $lastSyncAt,
            'timeLabel' => app()->isLocale('en') ? 'Venezuela time (GMT-4)' : 'Hora de Venezuela (GMT-4)',
        ])

        @php
            $monitoringPeriod = $obuMonitoringPeriod;
            $monitoringLabel = $monitoringPeriod?->period_start && $monitoringPeriod?->period_end
                ? $monitoringPeriod->period_start->locale('es')->isoFormat('MMMM').' – '.$monitoringPeriod->period_end->locale('es')->isoFormat('MMMM YYYY')
                : 'Período no disponible';
            $complaintsByGroup = collect($obuDatasets['documented_complaints'] ?? [])->groupBy('category');
            $ranking = collect($obuDatasets['university_ranking'] ?? []);
            $historical = collect($obuDatasets['historical_complaints'] ?? []);
            $sourceRows = collect($obuDatasets['complaint_sources'] ?? []);
            $newsRows = collect($obuDatasets['news_by_university_type'] ?? []);
            $newsChartCopy = [
                'no_controlled' => __('dashboard.obu.no_controlled'),
                'controlled' => __('dashboard.obu.controlled'),
                'quantity' => __('dashboard.obu.complaint_quantity'),
            ];
            $dashboardPayload = [
                'historical' => $historical,
                'sources' => $sourceRows,
                'news' => $newsRows,
                'newsChart' => $newsChartCopy,
            ];
            $datasetYears = $obuDatasetYears ?? range(2020, 2025);
            $selectedYear = 2025;
        @endphp

        <main class="obu-dashboard-public obu-page__main">
            <div class="jep-page__container">
                <section class="obu-editorial-grid obu-public-section" aria-label="Contenido editorial">@include('dashboard.organizations.partials.obu-editorial-cards') @if (false)
                    <article class="obu-editorial-card obu-editorial-card--note">
                        <div class="obu-editorial-card__copy"><span class="obu-public-eyebrow">Nota mensual</span><small>{{ $obuMonthlyNote['publication_date'] ?? 'Contenido editorial OBU' }}</small><h2>{{ $obuMonthlyNote['title'] ?? 'Nota mensual' }}</h2><p>{{ $obuMonthlyNote['excerpt'] ?? 'La próxima nota mensual del Observatorio de Universidades estará disponible próximamente.' }}</p>@if (! empty($obuMonthlyNote['url']))<a class="obu-public-link" href="{{ $obuMonthlyNote['url'] }}">Leer nota <i class="bi bi-arrow-right"></i></a>@endif</div>@if (! empty($obuMonthlyNote['image_url']))<img src="{{ $obuMonthlyNote['image_url'] }}" alt="" loading="lazy">@else<div class="obu-editorial-card__placeholder"><i class="bi bi-journal-text"></i></div>@endif
                    </article>
                    <article class="obu-editorial-card obu-editorial-card--alert">
                        <div class="obu-editorial-card__copy"><span class="obu-public-eyebrow">Alerta bimensual</span><small>@if ($obuBimonthlyAlert){{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_start'])->locale('es')->isoFormat('MMMM') }} – {{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_end'])->locale('es')->isoFormat('MMMM YYYY') }}@else Contenido editorial OBU @endif</small><h2>{{ $obuBimonthlyAlert['title'] ?? 'Alerta bimensual' }}</h2><p>{{ $obuBimonthlyAlert['excerpt'] ?? 'La próxima alerta bimensual del Observatorio de Universidades estará disponible próximamente.' }}</p>@if (! empty($obuBimonthlyAlert['file_url']))<a class="obu-public-link" href="{{ $obuBimonthlyAlert['file_url'] }}" download>Descargar alerta <i class="bi bi-download"></i></a>@endif</div>@if (! empty($obuBimonthlyAlert['image_url']))<img src="{{ $obuBimonthlyAlert['image_url'] }}" alt="" loading="lazy">@else<div class="obu-editorial-card__placeholder"><i class="bi bi-exclamation-triangle"></i></div>@endif
                    </article>
                @endif</section>

                <section class="obu-three-column-grid obu-public-section">
                    <article class="obu-data-card"><header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span><div><span class="obu-stat-card__title">Denuncias documentadas</span><p class="obu-stat-card__subtitle">Por derechos documentados</p></div></header>@foreach (['economic_social' => 'Derechos económicos, sociales y culturales', 'civil_political' => 'Derechos políticos y civiles'] as $groupKey => $groupLabel)<div class="obu-bar-group"><h3>{{ $groupLabel }}</h3>@php($items = $complaintsByGroup->get($groupKey, collect()))@php($max = max(1, (int) $items->max('value')))<div class="obu-bars">@foreach ($items as $item)<div class="obu-bar-row"><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong><div><i style="width: {{ ($item['value'] / $max) * 100 }}%"></i></div></div>@endforeach</div></div>@endforeach</article>
                    <article class="obu-data-card obu-protest-card"><header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-megaphone"></i></span><div><span class="obu-stat-card__title">Protestas universitarias</span><p class="obu-stat-card__subtitle">{{ number_format((int) ($obuMetrics?->protests ?? 0), 0, ',', '.') }} protestas registradas</p></div></header>@php($protestRows = collect($obuDatasets['protest_types'] ?? []))@php($max = max(1, (int) $protestRows->max('value')))<div class="obu-bars">@foreach ($protestRows as $item)<div class="obu-bar-row"><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong><div><i style="width: {{ ($item['value'] / $max) * 100 }}%"></i></div></div>@endforeach</div></article>
                    <article class="obu-data-card obu-chart-card"><header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span><div><span class="obu-stat-card__title">Evolución histórica 2020–2025</span><p class="obu-stat-card__subtitle">Denuncias por derechos</p></div></header><div class="obu-chart-wrap"><canvas id="obuHistoricalComplaintsChart" aria-label="Evolución histórica de denuncias" role="img"></canvas></div><div class="obu-chart-legend"><span><i class="obu-legend-dot obu-legend-dot--navy"></i> Derechos económicos y sociales</span><span><i class="obu-legend-dot obu-legend-dot--orange"></i> Derechos civiles y políticos</span></div></article>
                </section>

                <section class="obu-observatory-secondary-grid obu-observatory-secondary-grid--map-ranking obu-public-section">
                    <article class="obu-data-card obu-map-ranking-card">
                        <header class="obu-map-ranking-card__header text-center">
                            <span class="obu-map-ranking-card__title">UNIVERSIDADES M&Aacute;S RESE&Ntilde;ADAS EN EL &Uacute;LTIMO A&Ntilde;O</span>
                            <p class="obu-map-ranking-card__subtitle">Top 10 de menciones registradas</p>
                        </header>
                        <div class="obu-map-ranking-card__content">
                            <div class="obu-map-wrap">
                            <img src="{{ asset('assets/img/mapa-obu.png') }}" alt="Mapa de universidades monitoreadas por OBU" loading="lazy">
                            </div>
                    <article class="obu-data-card obu-ranking-card obu-ranking-section">
                        <div class="obu-ranking-section__content">
                            <div class="obu-ranking-table-wrap">
                            <table class="obu-ranking-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Universidad</th>
                                        <th>Menciones</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ranking as $index => $item)
                                        <tr class="{{ $index < 3 ? 'obu-ranking-row--top' : '' }}">
                                            <td>
                                                <span class="obu-ranking-number">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="obu-ranking-university">
                                                    <span class="obu-ranking-code">{{ $item['category'] }}</span>
                                                    <span class="obu-ranking-separator" aria-hidden="true">·</span>
                                                    <span class="obu-ranking-name">{{ $item['label'] }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $item['value'] }}</td>
                                            <td>{{ number_format((float) $item['percentage'], 0) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </article>
                        </div>
                    </article>
                </section>

                <section class="obu-observatory-secondary-grid obu-public-section">
                    <article class="obu-data-card obu-news-card"><header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-newspaper"></i></span><div><span class="obu-public-eyebrow">{{ __('dashboard.obu.complaints_by_university_type') }}</span><p>{{ __('dashboard.obu.distribution_by_year') }}</p></div></header><div class="obu-chart-wrap obu-chart-wrap--news"><canvas id="obuNewsTypeChart" aria-label="{{ __('dashboard.obu.complaints_by_university_type') }}" role="img"></canvas></div></article>
                    <article class="obu-data-card obu-source-chart-card">
                    <header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-people"></i></span><div><span class="obu-public-eyebrow">{{ __('dashboard.obu.who_reports') }}</span><p>Distribución por año</p></div></header><div class="obu-chart-wrap obu-chart-wrap--sources"><canvas id="obuComplaintSourcesChart" aria-label="{{ __('dashboard.obu.who_reports') }} - Distribución por año" role="img"></canvas></div>
                </section>

                <section class="obu-methodology-strip obu-public-section"><span class="obu-methodology-strip__icon"><i class="bi bi-info-circle"></i></span><div><strong>Fuente y nota metodológica</strong><p>Fuente: Monitoreo de prensa y fuentes abiertas realizado por el Observatorio de Universidades (OBU). Los datos corresponden al período seleccionado y están sujetos a actualización.</p></div></section>
            </div>
        </main>

        <script type="application/json" id="obuDashboardData">@json($dashboardPayload)</script>
    </div>

    @include('dashboard.partials.organization-footer', [
        'footerOrganization' => $organization,
        'footerCategory' => __('dashboard.university_title'),
        'footerAccent' => '#fd8700',
        'footerLinks' => [
            'website' => 'https://observatoriodeuniversidades.com/',
            'contact' => 'mailto:info@observatoriodeuniversidades.com',
            'info' => 'https://observatoriodeuniversidades.com/quienes-somos/',
            'facebook' => 'https://www.facebook.com/OBUniversidades/',
            'x' => 'https://x.com/obuvenezuela',
            'instagram' => 'https://www.instagram.com/obuniversidades',
            'youtube' => 'https://www.youtube.com/@obuniversidadestv3596',
            'tiktok' => '',
            'telegram' => '',
        ],
    ])
@endsection
