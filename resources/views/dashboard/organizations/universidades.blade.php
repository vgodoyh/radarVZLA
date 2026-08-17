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
            $obuResultGroups = [
                [
                    'modifier' => 'economic',
                    'title' => __('dashboard.obu.economic_social_cultural_rights'),
                    'icon' => 'bi-mortarboard-fill',
                    'total' => 99,
                    'items' => [
                        ['label' => __('dashboard.obu.decent_wages'), 'value' => 68, 'icon' => 'bi-currency-dollar'],
                        ['label' => __('dashboard.obu.infrastructure_damage'), 'value' => 19, 'icon' => 'bi-buildings-fill'],
                        ['label' => __('dashboard.obu.student_welfare'), 'value' => 12, 'icon' => 'bi-people-fill'],
                    ],
                ],
                [
                    'modifier' => 'political',
                    'title' => __('dashboard.obu.political_civil_rights'),
                    'icon' => 'bi-bank2',
                    'total' => 28,
                    'items' => [
                        ['label' => __('dashboard.obu.university_autonomy'), 'value' => 8, 'icon' => 'bi-bank2'],
                        ['label' => __('dashboard.obu.freedom_of_expression'), 'value' => 6, 'icon' => 'bi-chat-dots'],
                        ['label' => __('dashboard.obu.public_affairs_participation'), 'value' => 14, 'icon' => 'bi-hand-index-thumb'],
                    ],
                ],
                [
                    'modifier' => 'protests',
                    'title' => __('dashboard.obu.economic_rights_protests'),
                    'icon' => 'bi-megaphone-fill',
                    'total' => 75,
                    'items' => [
                        ['label' => __('dashboard.obu.strike'), 'value' => 29, 'icon' => 'bi-pause-circle-fill'],
                        ['label' => __('dashboard.obu.gathering'), 'value' => 20, 'icon' => 'bi-people-fill'],
                        ['label' => __('dashboard.obu.banner_protest'), 'value' => 7, 'icon' => 'bi-flag-fill'],
                        ['label' => __('dashboard.obu.march'), 'value' => 14, 'icon' => 'bi-person-walking'],
                        ['label' => __('dashboard.obu.other'), 'value' => 5, 'icon' => 'bi-three-dots'],
                    ],
                ],
            ];

            $obuSummary = [
                [
                    'modifier' => 'blue',
                    'label' => __('dashboard.obu.total_reports'),
                    'value' => 222,
                    'subtitle' => app()->isLocale('en') ? 'in the last 6 months' : 'en los últimos 6 meses',
                    'icon' => 'bi-file-earmark-bar-graph-fill',
                ],
                [
                    'modifier' => 'green',
                    'label' => __('dashboard.obu.documented_categories'),
                    'value' => 8,
                    'subtitle' => app()->isLocale('en') ? 'across 3 main areas' : 'en 3 áreas principales',
                    'icon' => 'bi-grid-fill',
                ],
                [
                    'modifier' => 'orange',
                    'label' => __('dashboard.obu.registered_protests'),
                    'value' => 75,
                    'subtitle' => app()->isLocale('en') ? 'across 5 modalities' : 'en 5 modalidades',
                    'icon' => 'bi-megaphone-fill',
                ],
            ];
        @endphp

        <main class="obu-monitoring-results obu-page__main">
            <div class="jep-page__container">
                <section class="obu-monitoring-panel">
                <div class="obu-summary-grid obu-monitoring-metrics" aria-label="{{ __('dashboard.obu.monitoring_results') }}">
                    @foreach ($obuSummary as $summary)
                        <article class="hero-kpi-card obu-summary-card obu-monitoring-metric obu-summary-card--{{ $summary['modifier'] }}">
                            <span class="obu-summary-card__icon obu-monitoring-metric__icon"><i class="bi {{ $summary['icon'] }}" aria-hidden="true"></i></span>
                            <div class="obu-monitoring-metric__content">
                                <strong class="obu-monitoring-metric__value">{{ $summary['value'] }}</strong>
                                <span class="obu-summary-card__title obu-monitoring-metric__label">{{ $summary['label'] }}</span>
                                <small class="obu-monitoring-metric__subtitle">{{ $summary['subtitle'] }}</small>
                            </div>
                        </article>
                    @endforeach
                </div>

                <header class="obu-monitoring-results__heading obu-monitoring-panel__heading" style="margin-top:60px; margin-bottom:20px;">
                    <h2>{{ __('dashboard.obu.monitoring_results') }}</h2>
                </header>

                <section class="obu-results-grid" aria-label="{{ __('dashboard.obu.monitoring_results') }}">
                    @foreach ($obuResultGroups as $group)
                        @php($maxValue = max(array_column($group['items'], 'value')))
                        <article class="obu-result-card obu-result-card--{{ $group['modifier'] }}">
                            <header class="obu-result-card__header">
                                <span class="obu-result-card__icon"><i class="bi {{ $group['icon'] }}" aria-hidden="true"></i></span>
                                <h2>{{ $group['title'] }}</h2>
                                <span class="obu-result-card__badge">OBU</span>
                            </header>

                            <div class="obu-result-card__columns" aria-hidden="true">
                                <span>{{ __('dashboard.obu.categories') }}</span>
                                <span>#</span>
                            </div>

                            <div class="obu-result-card__list">
                                @foreach ($group['items'] as $item)
                                    <div class="obu-result-row">
                                        <div class="obu-result-row__label">
                                            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <strong class="obu-result-row__value">{{ $item['value'] }}</strong>
                                        <div class="obu-result-row__progress" aria-hidden="true">
                                            <span style="--value: {{ ($item['value'] / $maxValue) * 100 }}%"></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <footer class="obu-result-card__total">
                                <span>{{ __('dashboard.obu.total') }}</span>
                                <strong>{{ $group['total'] }}</strong>
                            </footer>
                        </article>
                    @endforeach
                </section>

                <section class="obu-methodology-grid">
                    <article class="obu-methodology-card">
                        <span class="obu-methodology-card__icon"><i class="bi bi-info-circle" aria-hidden="true"></i></span>
                        <div>
                            <h2>{{ __('dashboard.obu.methodological_note') }}</h2>
                            <p>{{ __('dashboard.obu.methodological_text') }}</p>
                        </div>
                    </article>

                    <article class="obu-methodology-card obu-methodology-card--period">
                        <span class="obu-methodology-card__icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
                        <div>
                            <h2>{{ __('dashboard.obu.analysis_period') }}</h2>
                            <p>{{ __('dashboard.obu.analysis_period_text') }}</p>
                        </div>
                    </article>
                </section>
                </section>
            </div>
        </main>
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
