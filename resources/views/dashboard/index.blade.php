@extends('layouts.public')

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

                        <span class="hero-badge">
                            {{ __('dashboard.hero_badge') }}
                        </span>

                        <p class="hero-description">
                            {{ __('dashboard.hero_description') }}
                        </p>

                        <form
                            action="#"
                            method="GET"
                            class="hero-search"
                        >
                            <div class="hero-search__field">
                                <i class="bi bi-search"></i>

                                <input
                                    type="search"
                                    name="search"
                                    placeholder="{{ __('dashboard.search_placeholder') }}"
                                    aria-label="{{ __('dashboard.search_placeholder') }}"
                                >
                            </div>

                            <button
                                type="submit"
                                class="hero-search__button"
                            >
                                {{ __('dashboard.search_button') }}
                            </button>
                        </form>
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
                    @include('dashboard.partials.panorama')
                </section>

            </div>
        </main>

    </div>

    {{-- =====================================================
         FOOTER
    ====================================================== --}}
    <footer class="site-footer">
        <div class="site-footer__container">

            <div class="site-footer__top">

                <div class="site-footer__brand">
                    <div class="site-footer__logo-row">
                        <img
                            src="{{ asset('assets/img/isotipo-pulso.png') }}"
                            alt="Pulso Venezuela"
                            class="site-footer__logo"
                        >
                    </div>

                    <p>
                        {{ __('dashboard.footer_description') }}
                    </p>
                </div>

                <div class="site-footer__col">
                    <p class="site-footer__col-title">
                        {{ __('dashboard.name_organizations') }}
                    </p>

                    <div class="site-footer__links">
                        <a
                            href="https://accesoalajusticia.org"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Acceso a la Justicia
                        </a>

                        <a
                            href="https://fakenewsvenezuela.org"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Observatorio Fake News
                        </a>

                        <a
                            href="https://jepvenezuela.com"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            JEP Venezuela
                        </a>

                        <a
                            href="https://observatoriodeuniversidades.com"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Observatorio de Universidades
                        </a>
                    </div>
                </div>

                <div class="site-footer__col">
                    <div class="site-footer__status">
                        <span class="site-footer__status-dot"></span>

                        <span>
                            {{ __('dashboard.data_updated') }}
                        </span>
                    </div>

                    <p class="site-footer__sync">
                        {{ __('dashboard.last_sync') }}<br>

                        <strong>
                            {{ $lastSync ?? now()->format('d/m/Y, H:i') }}
                        </strong>
                    </p>
                </div>

            </div>

            <div class="site-footer__bottom">
                <p>
                    &copy; {{ date('Y') }} Pulso Venezuela.
                </p>
            </div>

        </div>
    </footer>

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
