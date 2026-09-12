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
                ? $monitoringPeriod->period_start->locale(app()->getLocale())->isoFormat('MMMM').' – '.$monitoringPeriod->period_end->locale(app()->getLocale())->isoFormat('MMMM YYYY')
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
            $sourceKeys = $sourceRows->pluck('label')->unique()->values();
            $sourceChartLabels = collect([
                __('dashboard.obu.reporter_student_representative'),
                __('dashboard.obu.reporter_university_authority'),
                __('dashboard.obu.reporter_faculty_representative'),
                __('dashboard.obu.reporter_union_representative'),
                __('dashboard.obu.reporter_university'),
                __('dashboard.obu.reporter_others'),
            ])->values()->map(function ($label, $index) use ($sourceKeys) {
                return ['key' => $sourceKeys->get($index), 'label' => $label];
            })->filter(fn (array $item) => filled($item['key']))->values();
            $dashboardPayload = [
                'historical' => $historical,
                'universityProtests' => collect($obuDatasets['university_protests_by_year'] ?? []),
                'sources' => $sourceRows,
                'news' => $newsRows,
                'newsChart' => $newsChartCopy,
                'sourceChart' => [
                    'series' => $sourceChartLabels,
                    'axisX' => __('dashboard.obu.year'),
                    'axisY' => __('dashboard.obu.count'),
                ],
                'complaintsRightsChart' => [
                    'economicSocial' => __('dashboard.obu.economic_social_rights'),
                    'civilPolitical' => __('dashboard.obu.civil_political_rights'),
                ],
                'protestsChart' => [
                    'title' => __('dashboard.obu.university_protests_history'),
                    'axis' => __('dashboard.obu.number_of_protests'),
                ],
            ];
            $datasetYears = $obuDatasetYears ?? range(2020, 2025);
            $selectedYear = 2025;
        @endphp

        <main class="obu-dashboard-public obu-page__main">
            <div class="jep-page__container">
                <section class="obu-editorial-grid obu-editorial-grid--single obu-public-section" aria-label="Contenido editorial">@include('dashboard.organizations.partials.obu-editorial-cards') @if (false)
                    <article class="obu-editorial-card obu-editorial-card--note">
                        <div class="obu-editorial-card__copy"><span class="obu-public-eyebrow">Nota mensual</span><small>{{ $obuMonthlyNote['publication_date'] ?? 'Contenido editorial OBU' }}</small><h2>{{ $obuMonthlyNote['title'] ?? 'Nota mensual' }}</h2><p>{{ $obuMonthlyNote['excerpt'] ?? 'La próxima nota mensual del Observatorio de Universidades estará disponible próximamente.' }}</p>@if (! empty($obuMonthlyNote['url']))<a class="obu-public-link" href="{{ $obuMonthlyNote['url'] }}">Leer nota <i class="bi bi-arrow-right"></i></a>@endif</div>@if (! empty($obuMonthlyNote['image_url']))<img src="{{ $obuMonthlyNote['image_url'] }}" alt="" loading="lazy">@else<div class="obu-editorial-card__placeholder"><i class="bi bi-journal-text"></i></div>@endif
                    </article>
                    <article class="obu-editorial-card obu-editorial-card--alert">
                        <div class="obu-editorial-card__copy"><span class="obu-public-eyebrow">Alerta bimensual</span><small>@if ($obuBimonthlyAlert){{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_start'])->locale('es')->isoFormat('MMMM') }} – {{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_end'])->locale('es')->isoFormat('MMMM YYYY') }}@else Contenido editorial OBU @endif</small><h2>{{ $obuBimonthlyAlert['title'] ?? 'Alerta bimensual' }}</h2><p>{{ $obuBimonthlyAlert['excerpt'] ?? 'La próxima alerta bimensual del Observatorio de Universidades estará disponible próximamente.' }}</p>@if (! empty($obuBimonthlyAlert['file_url']))<a class="obu-public-link" href="{{ $obuBimonthlyAlert['file_url'] }}" download>Descargar alerta <i class="bi bi-download"></i></a>@endif</div>@if (! empty($obuBimonthlyAlert['image_url']))<img src="{{ $obuBimonthlyAlert['image_url'] }}" alt="" loading="lazy">@else<div class="obu-editorial-card__placeholder"><i class="bi bi-exclamation-triangle"></i></div>@endif
                    </article>
                @endif</section>

                <section class="obu-historical-intro obu-public-section" aria-labelledby="obu-historical-intro-title">
                    <div class="obu-historical-intro__copy">
                        <span class="obu-historical-intro__eyebrow">{{ __('dashboard.obu.historical_data') }}</span>
                        <h2 id="obu-historical-intro-title">{{ __('dashboard.obu.historical_evolution') }}</h2>
                        <p>{{ __('dashboard.obu.historical_description') }}</p>
                        <span class="obu-historical-intro__period"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ __('dashboard.obu.historical_period') }}</span>
                    </div>
                    <div class="obu-historical-intro__decoration" aria-hidden="true">
                        <svg viewBox="0 0 520 220" role="presentation" focusable="false">
                            <defs>
                                <linearGradient id="obuHistoricalFade" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0" stop-color="#2373c8" stop-opacity=".18" />
                                    <stop offset="1" stop-color="#8bb9e8" stop-opacity=".02" />
                                </linearGradient>
                            </defs>
                            <path d="M15 185H505" fill="none" stroke="#2373c8" stroke-opacity=".08" />
                            <path d="M28 166L115 142L195 151L276 99L356 112L438 48L500 61V205H28Z" fill="url(#obuHistoricalFade)" />
                            <path d="M28 166L115 142L195 151L276 99L356 112L438 48L500 61" fill="none" stroke="#2373c8" stroke-opacity=".42" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <g fill="#2373c8" fill-opacity=".55">
                                <circle cx="28" cy="166" r="5" /><circle cx="115" cy="142" r="5" /><circle cx="195" cy="151" r="5" /><circle cx="276" cy="99" r="5" /><circle cx="356" cy="112" r="5" /><circle cx="438" cy="48" r="5" /><circle cx="500" cy="61" r="5" />
                            </g>
                            <g fill="#2373c8" fill-opacity=".07">
                                <rect x="55" y="115" width="34" height="70" rx="5" /><rect x="145" y="95" width="34" height="90" rx="5" /><rect x="235" y="72" width="34" height="113" rx="5" /><rect x="325" y="82" width="34" height="103" rx="5" /><rect x="415" y="42" width="34" height="143" rx="5" />
                            </g>
                        </svg>
                    </div>
                </section>

                <section class="row g-3 obu-historical-charts-row obu-public-section" aria-label="{{ __('dashboard.obu.university_protests_history') }}">
                    <div class="col-12 col-lg-6">
                        <article class="obu-data-card obu-chart-card obu-historical-protests-card">
                            <header class="obu-chart-card__header">
                                <span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                                <div>
                                    <span class="obu-stat-card__title">{{ __('dashboard.obu.university_protests_history') }}</span>
                                    <p>{{ __('dashboard.obu.distribution_by_year') }}</p>
                                </div>
                            </header>
                            <div class="obu-chart-wrap obu-chart-wrap--protests">
                                <canvas id="obuUniversityProtestsChart" aria-label="{{ __('dashboard.obu.university_protests_history') }}" role="img"></canvas>
                            </div>
                        </article>
                    </div>
                    <div class="col-12 col-lg-6">
                        <article class="obu-data-card obu-chart-card obu-historical-complaints-card">
                            <header class="obu-chart-card__header">
                                <span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                                <div>
                                    <span class="obu-stat-card__title">{{ __('dashboard.obu.complaints_by_rights') }}</span>
                                    <p>{{ __('dashboard.obu.distribution_by_year') }}</p>
                                </div>
                            </header>
                            <div class="obu-chart-wrap">
                                <canvas id="obuHistoricalComplaintsChart" aria-label="{{ __('dashboard.obu.historical_evolution') }}" role="img"></canvas>
                            </div>
                            <div class="obu-chart-legend">
                                <span><i class="obu-legend-dot obu-legend-dot--navy"></i> {{ __('dashboard.obu.economic_social_cultural_rights') }}</span>
                                <span><i class="obu-legend-dot obu-legend-dot--orange"></i> {{ __('dashboard.obu.political_civil_rights') }}</span>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="obu-observatory-secondary-grid obu-observatory-secondary-grid--map-ranking obu-public-section">
                    <article class="obu-data-card obu-map-ranking-card">
                        <header class="obu-map-ranking-card__header">
                            <span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-mortarboard"></i></span>
                            <div>
                                <span class="obu-map-ranking-card__title">{{ __('dashboard.obu.most_mentioned_universities') }}</span>
                                <p class="obu-map-ranking-card__subtitle">{{ __('dashboard.obu.top_ten_mentions') }}</p>
                            </div>
                        </header>
                        <div class="obu-map-ranking-card__content">
                            <div class="obu-map-wrap">
                                <img src="{{ asset('assets/img/mapa-obu.png') }}" alt="{{ __('dashboard.obu.university_mentions_map') }}" loading="lazy">
                                <div class="obu-map-ranking-card__map-note"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ __('dashboard.obu.most_mentioned_location') }}</span></div>
                            </div>
                            <div class="obu-ranking-section">
                                <div class="obu-ranking-section__content">
                                    <div class="obu-ranking-table-wrap">
                                        <span class="obu-ranking-eyebrow">{{ __('dashboard.obu.top_ten') }}</span>
                                        <table class="obu-ranking-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ __('dashboard.obu.university') }}</th>
                                                    <th>{{ __('dashboard.obu.mentions') }}</th>
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
                            </div>
                        </div>
                    </article>
                </section>

                <section class="obu-observatory-secondary-grid obu-public-section">
                    <article class="obu-data-card obu-news-card">
                        <header class="obu-chart-card__header">
                            <span class="obu-chart-card__icon" aria-hidden="true">
                                <i class="bi bi-newspaper"></i>
                            </span>
                            <div>
                                <span class="obu-public-eyebrow">{{ __('dashboard.obu.complaints_by_university_type') }}</span>
                                <p>{{ __('dashboard.obu.distribution_by_year') }}</p>
                            </div>
                        </header>
                        <div class="obu-chart-wrap obu-chart-wrap--news">
                            <canvas id="obuNewsTypeChart" aria-label="{{ __('dashboard.obu.complaints_by_university_type') }}" role="img"></canvas>
                        </div>
                    </article>
                    <article class="obu-data-card obu-source-chart-card">
                    <header class="obu-chart-card__header"><span class="obu-chart-card__icon" aria-hidden="true"><i class="bi bi-people"></i></span><div><span class="obu-public-eyebrow">{{ __('dashboard.obu.who_reports') }}</span><p>Distribución por año</p></div></header><div class="obu-chart-wrap obu-chart-wrap--sources"><canvas id="obuComplaintSourcesChart" aria-label="{{ __('dashboard.obu.who_reports') }} - Distribución por año" role="img"></canvas></div>
                </section>

                <section class="obu-methodology-strip obu-public-section"><span class="obu-methodology-strip__icon"><i class="bi bi-info-circle"></i></span><div><strong>{{ __('dashboard.obu.methodology_title') }}</strong><p>{{ __('dashboard.obu.methodology_text') }}</p></div></section>
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
