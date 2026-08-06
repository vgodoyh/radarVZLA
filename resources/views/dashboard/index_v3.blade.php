@extends('layouts.public_v3')

@section('title', __('dashboard.meta_title'))

@section('content')

    <div class="home-surface">

        {{-- =====================================================
             HERO
        ====================================================== --}}
        <header class="hero-section hero-section--light">
            <div class="container-fluid px-4 px-xl-5 py-4">

                {{-- Selector de idioma --}}
                <div class="d-flex justify-content-end mb-1">
                    <div
                        class="language-switcher language-switcher--light"
                        aria-label="{{ __('dashboard.language') }}"
                    >
                        <i class="bi bi-globe2"></i>

                        <a
                            href="{{ route('language.switch', 'es') }}"
                            class="{{ app()->isLocale('es') ? 'active' : '' }}"
                        >
                            ES
                        </a>

                        <span class="language-switcher__separator">|</span>

                        <a
                            href="{{ route('language.switch', 'en') }}"
                            class="{{ app()->isLocale('en') ? 'active' : '' }}"
                        >
                            EN
                        </a>
                    </div>
                </div>

                <div class="row align-items-start g-4">

                    {{-- Contenido principal --}}
                    <div class="col-12 col-lg-6">

                        <a
                            href="{{ route('dashboard.public') }}"
                            class="hero-isotype d-inline-flex text-decoration-none"
                            aria-label="{{ __('dashboard.site_name') }}"
                        >
                            <img
                                src="{{ asset('assets/img/isotipo-pulso.png') }}"
                                alt="{{ __('dashboard.site_name') }}"
                            >
                        </a>

                        <h1 class="hero-title">
                            {{ __('dashboard.hero_title_1') }}<br>
                            {{ __('dashboard.hero_title_2') }}<br>

                            <span>
                                {{ __('dashboard.hero_title_3') }}
                            </span>
                        </h1>

                        <p class="hero-description">
                            {{ __('dashboard.hero_description') }}
                        </p>
                    </div>

                    {{-- Mapa y actualización --}}
                    <div class="col-12 col-lg-6 position-relative hero-map-column">

                        <div class="hero-map" aria-hidden="true">
                            <img
                                src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}"
                                alt=""
                            >
                        </div>

                        <div class="hero-update-card">
                            <div class="hero-update-card__heading">
                                <i class="bi bi-calendar2-check"></i>

                                <span>
                                    {{ __('dashboard.data_updated') }}
                                </span>
                            </div>

                            <div class="hero-update-card__date">
                                {{ now('America/Caracas')
                                    ->locale(app()->getLocale())
                                    ->translatedFormat('d M Y') }}
                            </div>

                            <div class="hero-update-card__divider"></div>

                            <div class="hero-update-card__time">
                                <i class="bi bi-clock"></i>
                                <span id="rv-time"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- =====================================================
             PANORAMA
        ====================================================== --}}
        <main class="dashboard-main">
            <div class="dashboard-main__container px-4 px-xl-5">

                <section
                    id="panorama"
                    class="dashboard-panorama"
                >
                    @include('dashboard.partials.panorama_v3')
                </section>

            </div>
        </main>

    </div>

    @include('dashboard.partials.footer', [
        'lastSync' => $lastSync ?? null,
    ])

    <script>
        function rvUpdateClock() {
            const now = new Date();

            const locale =
                document.documentElement.lang === 'en'
                    ? 'en-US'
                    : 'es-VE';

            const timeElement = document.getElementById('rv-time');

            if (!timeElement) {
                return;
            }

            timeElement.textContent = now.toLocaleTimeString(locale, {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        rvUpdateClock();

        setInterval(rvUpdateClock, 30000);
    </script>

@endsection
