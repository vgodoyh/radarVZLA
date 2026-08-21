@extends('layouts.public_v2')

@section('title', __('dashboard.meta_title'))

@section('content')
    @php
        $lastSyncAt = filled($lastSync ?? null)
            ? rescue(
                fn () => \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale()),
                null,
                false
            )
            : null;

        $pulseStats = [
            [
                'class' => 'jep',
                'value' => data_get($stats ?? [], '0.value'),
                'label' => __('dashboard.dashboard_v2.political_prisoners'),
                'organization' => 'JEP',
            ],
            [
                'class' => 'acceso',
                'value' => $accesoLegalPublicationsTotal ?? 0,
                'label' => __('dashboard.dashboard_v2.legal_alerts'),
                'organization' => __('dashboard.dashboard_v2.acceso_short'),
            ],
            [
                'class' => 'ovfn',
                'value' => '137',
                'label' => __('dashboard.dashboard_v2.verifications'),
                'organization' => 'OVFN',
            ],
            [
                'class' => 'obu',
                'value' => collect($economicSocialItems ?? [])->concat($civilPoliticalItems ?? [])->sum('value'),
                'label' => __('dashboard.dashboard_v2.university_complaints'),
                'organization' => 'OBU',
            ],
        ];
    @endphp

    <div class="home-surface dashboard-v2">
        @include('dashboard.partials.global-header', ['headerAccent' => '#42b9e8'])

        <header class="dashboard-v2-header">
            <div class="dashboard-v2-container">
                <div class="dashboard-v2-partners" aria-label="{{ __('dashboard.participating_organizations') }}">
                    <div class="dashboard-v2-partner dashboard-v2-partner--jep">
                        <img src="{{ asset('assets/img/organizations/jep.svg') }}" alt="Justicia, Encuentro y Perdón" loading="eager">
                    </div>
                    <div class="dashboard-v2-partner dashboard-v2-partner--acceso">
                        <img src="{{ asset('assets/img/organizations/acceso-justicia.png') }}" alt="Acceso a la Justicia" loading="eager">
                    </div>
                    <div class="dashboard-v2-partner dashboard-v2-partner--fake-news">
                        <img src="{{ asset('assets/img/organizations/fake-news.png') }}" alt="Observatorio Venezolano de Fake News" loading="eager">
                    </div>
                    <div class="dashboard-v2-partner dashboard-v2-partner--obu">
                        <img src="{{ asset('assets/img/organizations/obu.png') }}" alt="Observatorio de Universidades" loading="eager">
                    </div>
                </div>

                <div class="dashboard-v2-hero">
                    <div class="dashboard-v2-hero-content">
                        <h1>
                            {{ __('dashboard.dashboard_v2.hero_line_1') }}<br>
                            {{ __('dashboard.dashboard_v2.hero_line_2') }}<br>
                            {{ __('dashboard.dashboard_v2.hero_line_3') }}
                        </h1>
                        <div class="dashboard-v2-hero__country">
                            {{ __('dashboard.dashboard_v2.in_venezuela') }}
                        </div>
                        <span class="dashboard-v2-hero__line" aria-hidden="true"></span>
                        <div class="dashboard-v2-description">
                            <p>{{ __('dashboard.hero_description') }}</p>
                        </div>
                        <div class="dashboard-v2-update">
                            <span class="dashboard-v2-update__dot" aria-hidden="true"></span>
                            <span>{{ __('dashboard.data_updated') }}:</span>
                            <strong>{{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}</strong>
                        </div>
                    </div>

                    <div class="dashboard-v2-map" aria-hidden="true">
                        <img
                            src="{{ asset('assets/img/mapa-venezuela-pulso-final.svg') }}"
                            alt="Mapa de Venezuela"
                            class="hero-map-image"
                        >
                    </div>
                </div>
            </div>
        </header>

        <section class="dashboard-v2-pulse" aria-labelledby="dashboard-v2-pulse-title">
            <div class="dashboard-v2-container">
                <div class="dashboard-v2-pulse__heading">
                    <span aria-hidden="true"></span>
                    <h2 id="dashboard-v2-pulse-title">{{ __('dashboard.dashboard_v2.pulse_title') }}</h2>
                    <span aria-hidden="true"></span>
                </div>

                <div class="dashboard-v2-pulse__grid">
                    @foreach ($pulseStats as $pulseStat)
                        <article class="dashboard-v2-stat dashboard-v2-stat--{{ $pulseStat['class'] }}">
                            <strong>{{ filled($pulseStat['value']) ? $pulseStat['value'] : '—' }}</strong>
                            <p>{{ $pulseStat['label'] }}</p>
                            <span>{{ $pulseStat['organization'] }}</span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <main class="dashboard-main">
            <div class="dashboard-main__container dashboard-v2-container">
                <section id="panorama" class="dashboard-panorama">
                    @include('dashboard.partials.panorama_v2')
                </section>
            </div>
        </main>
    </div>

    @include('dashboard.partials.footer', [
        'lastSync' => $lastSync ?? null,
        'containerClass' => 'dashboard-v2-container dashboard-v2-footer-container',
        'footerClass' => 'site-footer--home',
    ])
@endsection
