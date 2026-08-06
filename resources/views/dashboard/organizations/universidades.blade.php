@extends('layouts.public')

@section('title', 'Observatorio de Universidades | Pulso Venezuela')

@section('content')
    @php
        $lastSyncAt = filled($lastSync ?? null)
            ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
            : null;
    @endphp

    <div class="organization-page organization-page--obu obu-page">
        <header class="jep-page__hero hero-section--light obu-page__hero">
            <div class="jep-page__container">
                <nav class="jep-page__topbar">
                    <a href="{{ route('dashboard.public') }}" class="hero-isotype" aria-label="{{ __('dashboard.site_name') }}">
                        <img src="{{ asset('assets/img/isotipo-pulso.png') }}" alt="{{ __('dashboard.site_name') }}">
                    </a>

                    <div class="language-switcher language-switcher--light" aria-label="{{ __('dashboard.language') }}">
                        <i class="bi bi-globe2" aria-hidden="true"></i>
                        <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}">ES</a>
                        <span class="language-switcher__separator">|</span>
                        <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}">EN</a>
                    </div>
                </nav>

                <div class="jep-page__hero-grid">
                    <div class="jep-page__hero-content">
                        <div class="jep-page__identity">
                            <span class="jep-page__logo">
                                <img src="{{ $organization['logo'] }}" alt="{{ $organization['name'] }}">
                            </span>

                            <div>
                                <span class="jep-page__eyebrow obu-hero__badge">{{ __('dashboard.university_title') }}</span>
                                <h1>{{ $organization['name'] }}</h1>
                                <p class="jep-page__description">{{ __('dashboard.university_description') }}</p>

                                <div class="jep-page__links">
                                    @if (filled($organization['username'] ?? null))
                                        <a href="https://x.com/{{ $organization['username'] }}" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-twitter-x" aria-hidden="true"></i>
                                            {{ '@'.$organization['username'] }}
                                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                        </a>
                                    @endif

                                    @if (filled($organization['website_url'] ?? null))
                                        <a href="{{ $organization['website_url'] }}" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-globe2" aria-hidden="true"></i>
                                            {{ preg_replace('#^https?://(www\.)?#', '', rtrim($organization['website_url'], '/')) }}
                                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="jep-page__visual">
                        <div class="jep-page__map" aria-hidden="true">
                            <img src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}" alt="">
                        </div>

                        <div class="hero-update-card jep-page__update-card">
                            <div class="hero-update-card__heading">
                                <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                                <span>{{ __('dashboard.data_updated') }}</span>
                            </div>
                            <div class="hero-update-card__date {{ $lastSyncAt ? 'hero-update-card__date--synced' : '' }}">
                                {{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}
                            </div>
                            @if ($lastSyncAt)
                                <div class="hero-update-card__divider"></div>
                                <div class="hero-update-card__time">
                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                    <span>{{ $lastSyncAt->translatedFormat('h:i a') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

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
                [
                    'modifier' => 'purple',
                    'label' => __('dashboard.obu.analysis_period'),
                    'value' => __('dashboard.obu.january_june_2026'),
                    'subtitle' => app()->isLocale('en') ? 'Last 6 months' : 'Últimos 6 meses',
                    'icon' => 'bi-calendar3',
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

    @include('dashboard.partials.footer', [
        'lastSync' => $lastSync ?? null,
    ])
@endsection
