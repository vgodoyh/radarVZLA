@extends('layouts.public')

@section('title', __('dashboard.meta_title'))

@section('content')
    <header class="hero-section hero-section--light">
        <div class="container-fluid px-4 px-xl-5 py-4">

        {{-- Selector de idioma --}}
        <div class="d-flex justify-content-end mb-1">
            <div class="language-switcher language-switcher--light"
                aria-label="{{ __('dashboard.language') }}">
                <i class="bi bi-globe2"></i>
                <a href="{{ route('language.switch', 'es') }}"
                    class="{{ app()->isLocale('es') ? 'active' : '' }}">
                    ES
                </a>
                <span class="language-switcher__separator">|</span>
                <a href="{{ route('language.switch', 'en') }}"
                    class="{{ app()->isLocale('en') ? 'active' : '' }}">
                    EN
                </a>
            </div>
        </div>

        <div class="row align-items-start g-4">
            {{-- Contenido principal --}}
            <div class="col-12 col-lg-6">
                {{-- Isotipo --}}
                <a href="{{ route('dashboard.public') }}"
                    class="hero-isotype d-inline-flex text-decoration-none"
                    aria-label="{{ __('dashboard.site_name') }}">
                    <img src="{{ asset('assets/img/isotipo-pulso.png') }}"
                        alt="{{ __('dashboard.site_name') }}">
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

                {{-- Buscador --}}
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

                    <button type="submit" class="hero-search__button">
                        {{ __('dashboard.search_button') }}
                    </button>
                </form>
            </div>

            {{-- Mapa y actualización --}}
            <div class="col-12 col-lg-6 position-relative">

                <div class="hero-map" aria-hidden="true">
                    <img
                        src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}"
                        alt="">
                </div>

                <div class="hero-update-card">
                    <div class="hero-update-card__heading">
                        <i class="bi bi-calendar2-check"></i>

                        <span>
                            {{ __('dashboard.data_updated') }}
                        </span>
                    </div>

                    <div class="hero-update-card__date">
                        {{ now()->locale(app()->getLocale())->translatedFormat('d M Y') }}
                    </div>

                    <div class="hero-update-card__divider"></div>

                    <div class="hero-update-card__time">
                        <i class="bi bi-clock"></i>

                        <span id="rv-time"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organizaciones participantes --}}
        <section class="organizations-showcase">
            <div class="row g-3 align-items-stretch">

                <div class="col-12 col-xl-3">
                    <div class="organizations-showcase__intro">
                        <h2>{{ __('dashboard.participating_organizations') }}</h2>

                        <p>
                            {{ __('dashboard.participating_organizations_description') }}
                        </p>

                        <p class="text-blue">
                            {{ __('dashboard.learn_more_about_us') }}
                            <i class="bi bi-arrow-right"></i>
                        </p>
                    </div>
                </div>

                @foreach (__('dashboard.organizations') as $organization)
                    <div class="col-12 col-sm-6 col-xl">
                        <a
                            href="{{ $organization['url'] }}"
                            class="organization-showcase-card"
                        >
                            <div class="organization-showcase-card__logo">
                                <img
                                    src="{{ asset($organization['logo']) }}"
                                    class="{{ $organization['slug'] ?? '' }}"
                                    alt="{{ $organization['name'] }}"
                                >
                            </div>

                            <div class="organization-showcase-card__content">
                                <h3>{{ $organization['name'] }}</h3>
                                <p>{{ $organization['area'] }}</p>
                            </div>

                            <span class="organization-showcase-card__arrow">
                                <i class="bi bi-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                @endforeach

            </div>
        </section>

        {{-- Valores institucionales --}}
        <div class="hero-values mt-3">
            @foreach (__('dashboard.values') as $value)
                <div class="hero-value">
                    <div class="hero-value__icon">
                        <i class="{{ $value['icon'] }}"></i>
                    </div>

                    <div>
                        <strong>{{ $value['title'] }}</strong>
                        <span>{{ $value['description'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Indicadores principales --}}
        <div class="row g-3 mt-3">

            <div class="col-12 col-md-6 col-xl">
                <article class="hero-kpi-card h-100">
                    <div class="hero-kpi-card__icon hero-kpi-card__icon--blue">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <strong class="hero-kpi-card__value">1.875</strong>

                        <p class="hero-kpi-card__label">
                            {{ __('dashboard.stats.political_prisoners') }}
                        </p>

                        <span class="hero-kpi-card__trend hero-kpi-card__trend--danger">
                            <i class="bi bi-arrow-up"></i>
                            12
                            {{ __('dashboard.this_month') }}
                        </span>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl">
                <article class="hero-kpi-card h-100">
                    <div class="hero-kpi-card__icon hero-kpi-card__icon--green">
                        <i class="bi bi-unlock-fill"></i>
                    </div>

                    <div>
                        <strong class="hero-kpi-card__value">87</strong>

                        <p class="hero-kpi-card__label">
                            {{ __('dashboard.stats.releases') }}
                        </p>

                        <span class="hero-kpi-card__trend hero-kpi-card__trend--success">
                            <i class="bi bi-arrow-up"></i>
                            18
                            {{ __('dashboard.this_month') }}
                        </span>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl">
                <article class="hero-kpi-card h-100">
                    <div class="hero-kpi-card__icon hero-kpi-card__icon--orange">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>

                    <div>
                        <strong class="hero-kpi-card__value">142</strong>

                        <p class="hero-kpi-card__label">
                            {{ __('dashboard.stats.seriously_ill') }}
                        </p>

                        <span class="hero-kpi-card__trend hero-kpi-card__trend--danger">
                            <i class="bi bi-arrow-up"></i>
                            5
                            {{ __('dashboard.this_month') }}
                        </span>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl">
                <article class="hero-kpi-card h-100">
                    <div class="hero-kpi-card__icon hero-kpi-card__icon--cyan">
                        <i class="bi bi-globe-americas"></i>
                    </div>

                    <div>
                        <strong class="hero-kpi-card__value">23</strong>

                        <p class="hero-kpi-card__label">
                            {{ __('dashboard.stats.foreign_dual_nationals') }}
                        </p>

                        <span class="hero-kpi-card__trend hero-kpi-card__trend--success">
                            <i class="bi bi-arrow-up"></i>
                            3
                            {{ __('dashboard.this_month') }}
                        </span>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl">
                <article class="hero-kpi-card h-100">
                    <div class="hero-kpi-card__icon hero-kpi-card__icon--purple">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>

                    <div>
                        <strong class="hero-kpi-card__value">4</strong>

                        <p class="hero-kpi-card__label">
                            {{ __('dashboard.organizations_monitored') }}
                        </p>

                        <span class="hero-kpi-card__trend">
                            —
                            {{ __('dashboard.no_changes') }}
                        </span>
                    </div>
                </article>
            </div>

        </div>

        

    </div>

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

            timeElement.textContent = now.toLocaleTimeString(
                locale,
                {
                    hour: '2-digit',
                    minute: '2-digit'
                }
            );
        }

        rvUpdateClock();

        setInterval(rvUpdateClock, 30000);
    </script>
</header>

    <main class="container-fluid px-4 px-xl-5 py-4 dashboard-shell">
        <div class="tagline-wrapper">
            <p class="tagline mb-4">
                <span>{{ __('dashboard.tagline_1') }}</span>
                <span class="tagline__dot"></span>
                <span>{{ __('dashboard.tagline_2') }}</span>
                <span class="tagline__dot"></span>
                <span>{{ __('dashboard.tagline_3') }}</span>
            </p>
        </div>

        <hr class="horizontal light mt-0">

        {{-- JEP --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner_jep">
                    <span class="section-eyebrow-jep">
                        {{ __('dashboard.jep') }}
                    </span>

                    <h2 class="text-jep-light">{{ __('dashboard.jep_title') }}</h2>
                </div>
            </div>
        </section>
        {{-- CARD ESTADISTICOS --}}
        <section class="mb-4" style="margin-top:50px;">
            <div class="subsection-heading">
                <div class="subsection-heading__top">
                    <span class="subsection-heading__dot_jep"></span>
                    <span class="subsection-heading__label">{{ __('dashboard.key_figures') }}</span>
                </div>
            </div>

            <div class="row g-3 align-items-stretch mb-4">
                {{-- Columna izquierda: total + desglose 2x2, y debajo la nota de excarcelaciones --}}
                <div class="col-12 col-lg-6 d-flex flex-column gap-3">
                    <div class="kpi-split-panel">

                        <div class="kpi-split-panel__primary">
                            <div class="kpi-primary__label">{{ __('dashboard.stats.political_prisoners') }}</div>
                            <div class="kpi-primary__value">1.875</div>
                            <span class="kpi-primary__pill">
                                <i class="bi bi-arrow-up"></i>+5,2%
                            </span>
                        </div>

                        <div class="kpi-split-panel__grid">

                            <div class="kpi-item">
                                <div class="kpi-item__label">{{ __('dashboard.stats.women') }}</div>
                                <div class="kpi-item__row">
                                    <span class="kpi-item__value">234</span>
                                    <span class="kpi-item__trend">
                                        <i class="bi bi-caret-up-fill"></i>6,4%
                                    </span>
                                </div>
                                <span class="kpi-item__underline"></span>
                            </div>

                            <div class="kpi-item">
                                <div class="kpi-item__label">{{ __('dashboard.stats.seriously_ill') }}</div>
                                <div class="kpi-item__row">
                                    <span class="kpi-item__value">142</span>
                                    <span class="kpi-item__trend">
                                        <i class="bi bi-caret-up-fill"></i>18,3%
                                    </span>
                                </div>
                                <span class="kpi-item__underline"></span>
                            </div>

                            <div class="kpi-item">
                                <div class="kpi-item__label">{{ __('dashboard.stats.foreign_dual_nationals') }}</div>
                                <div class="kpi-item__row">
                                    <span class="kpi-item__value">23</span>
                                    <span class="kpi-item__trend">
                                        <i class="bi bi-caret-up-fill"></i>21,1%
                                    </span>
                                </div>
                                <span class="kpi-item__underline"></span>
                            </div>

                            <div class="kpi-item kpi-item--positive">
                                <div class="kpi-item__label">{{ __('dashboard.stats.releases') }}</div>
                                <div class="kpi-item__row">
                                    <span class="kpi-item__value">87</span>
                                    <span class="kpi-item__trend">
                                        <i class="bi bi-caret-up-fill"></i>12,9%
                                    </span>
                                </div>
                                <span class="kpi-item__underline"></span>
                            </div>

                        </div>
                    </div>

                    <div class="kpi-note-card">
                        <div class="kpi-note-card__header">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>{{ __('dashboard.releases_methodology_label') }}</span>
                        </div>
                        <p class="kpi-note-card__text">{{ __('dashboard.releases_methodology_note') }}</p>
                    </div>
                </div>

                {{-- Columna derecha: criterios de contabilización --}}
                <div class="col-12 col-lg-6">
                    <div class="key-figures-panel__criteria">
                        <div class="key-figures-panel__criteria-header">
                            <i class="bi bi-clipboard-check text-jep-light"></i>
                            <span>{{ __('dashboard.key_figures_criteria_label') }}</span>
                        </div>

                        <p class="key-figures-panel__criteria-intro">{{ __('dashboard.key_figures_intro') }}</p>

                        <div class="criteria-grid">
                            @foreach (__('dashboard.key_figures_criteria') as $index => $criterion)
                                <div class="criteria-item">
                                    <span class="criteria-item__num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <p class="criteria-item__text">{{ $criterion }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="key-figures-panel__criteria-note">{{ __('dashboard.key_figures_note') }}</p>
                    </div>
                </div>

            </div>

            <div class="section-card featured-section mb-4">

                <div class="row g-4 align-items-stretch">
                    <div class="col-12 col-lg-7">
                        <div class="chart-panel h-100">
                            <canvas id="featuredChart"></canvas>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="analysis-panel h-100">
                            <div class="subsection-heading">
                                <div class="subsection-heading__top">
                                    <span class="subsection-heading__dot_jep"></span>
                                    <span class="subsection-heading__label">{{ __('dashboard.featured_indicator') }}</span>
                                </div>
                            </div>
                            <p>{{ __('dashboard.featured_analysis') }}</p>
                            <div class="slim-links">
                                <a href="#"><i class="bi bi-file-earmark-text"></i>{{ __('dashboard.press_release') }}<i class="bi bi-arrow-up-right"></i></a>
                                <a href="#"><i class="bi bi-twitter-x"></i>{{ __('dashboard.x_thread') }}<i class="bi bi-arrow-up-right"></i></a>
                                <a href="#"><i class="bi bi-globe2"></i>{{ __('dashboard.full_website') }}<i class="bi bi-arrow-up-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <section class="mb-4" style="margin-top: 30px;" >
            <div class="subsection-heading">
                <div class="subsection-heading__top">
                    <span class="subsection-heading__dot"></span>
                    <span class="subsection-heading__label">{{ __('dashboard.indicator_groups') }}</span>
                </div>
                <p class="subsection-heading__desc">{{ __('dashboard.explore_data') }}</p>
            </div>

            <div class="row g-3">
                @foreach (__('dashboard.groups') as $group)
                    <div class="col-12 col-md-6 col-xl-3">
                        <article class="indicator-card h-100">
                            <div class="indicator-card__header">
                                <div class="indicator-card__icon"><i class="{{ $group['icon'] }}"></i></div>
                                <h3 class="indicator-card__title">{{ $group['title'] }}</h3>
                            </div>

                            <div class="indicator-card__tags">
                                @foreach ($group['items'] as $item)
                                    <span class="indicator-card__tag">{{ $item }}</span>
                                @endforeach
                            </div>

                            <a href="#" class="indicator-card__link">
                                {{ __('dashboard.view_indicators') }}
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>  

        {{-- ACCESO A LA JUSTICIA --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner">
                    <span class="section-eyebrow">
                        {{ __('dashboard.acceso_justicia') }}
                    </span>
                    <h2>{{ __('dashboard.accesojusticia_title') }}</h2>
                </div>
            </div>
            <div class="row g-3">
                {{--TWEET X ALERTA LEGAL --}}
                <div class="col-6">                                            
                    <article class="organization-feed h-100">

                        <header class="organization-feed__header">
                            <div class="organization-feed__identity">
                                <div class="organization-feed__logo">
                                    <img
                                        src="{{ $organizations[1]['logo_x'] }}"
                                        alt="{{ $organizations[1]['name'] }}"
                                    >
                                </div>

                                <div class="organization-feed__meta">
                                    <h3>{{ $organizations[1]['name'] }}</h3>

                                    <a
                                        href="https://x.com/{{ $organizations[1]['username'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {{ '@' . $organizations[1]['username'] }}
                                    </a>
                                    <div class="alert-legal ms-2">
                                        <span>#AlertaLegal</span>
                                    </div>
                                </div>
                            </div>
                        </header>
                        <div class="organization-feed__posts">
                            @forelse ($alertasLegales as $alerta)                                
                                <a
                                        href="{{ $alerta['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="feed-post"
                                    >
                                    <div class="feed-post__image">
                                        @if ($alerta['image'])
                                            <img
                                                src="{{ $alerta['image'] }}"
                                                alt=""
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="feed-post__placeholder">
                                                <i class="bi bi-twitter-x"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="feed-post__content">
                                        <p class="feed-post__title">
                                            {{ \Illuminate\Support\Str::limit(
                                                $alerta['text'],
                                                150
                                            ) }}
                                        </p>

                                        @if ($alerta['created_at'])
                                            <time class="feed-post__time">
                                                {{ \Carbon\Carbon::parse($alerta['created_at'])->diffForHumans() }}
                                            </time>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="feed-empty">
                                    <i class="bi bi-info-circle"></i>

                                    <p>
                                        No fue posible cargar las publicaciones
                                        de esta organización.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        <footer class="organization-feed__footer">
                            <a
                                href="https://x.com/{{ $organizations[1]['username'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ __('dashboard.view_on_x') }}

                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </footer>
                    </article>
                </div>

                {{-- ÚLTIMO ALERTALEGAL --}}
                <div class="col-6">
                    <div class="article-panel article-panel--prensa">
                        <div class="article-panel__header">
                            <div class="article-panel__heading">
                                <span class="article-panel__dot"></span>
                                <p class="article-panel__title">
                                    {{ __('dashboard.last_post') }}
                                </p>
                            </div>

                            @if (count($alertasLegales) > 0 && $alertasLegales[0]['created_at'])
                                <span class="article-panel__count">
                                    {{ \Carbon\Carbon::parse($alertasLegales[0]['created_at'])->translatedFormat('d M, H:i') }}
                                </span>
                            @endif
                        </div>

                        <div>
                            @if (count($alertasLegales) > 0)
                                @php
                                    $tweet = $alertasLegales[0];

                                    // Escapamos el texto completo (sin recortar ni colapsar saltos de línea)
                                    $safeText = e($tweet['text']);

                                    // Resaltamos hashtags en azul
                                    $highlighted = preg_replace(
                                        '/#(\w+)/u',
                                        '<span class="prensa-post__hashtag">#$1</span>',
                                        $safeText
                                    );
                                @endphp

                                <div class="prensa-post">
                                    <div class="prensa-post__image">
                                        @if ($tweet['image'])
                                            <img src="{{ $tweet['image'] }}" alt="" loading="lazy">
                                        @else
                                            <div class="prensa-post__placeholder">
                                                <i class="bi bi-twitter-x"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="prensa-post__content">
                                        <p class="prensa-post__text">
                                            {!! $highlighted !!}
                                        </p>

                                        <div class="prensa-post__meta">
                                            @if ($tweet['created_at'])
                                                <time class="prensa-post__time">
                                                    {{ \Carbon\Carbon::parse($tweet['created_at'])->diffForHumans() }}
                                                </time>
                                            @endif

                                            <span class="prensa-post__stat">
                                                <i class="bi bi-heart"></i> {{ $tweet['likes'] }}
                                            </span>
                                            <span class="prensa-post__stat">
                                                <i class="bi bi-repeat"></i> {{ $tweet['retweets'] }}
                                            </span>
                                        </div>

                                        <div class="prensa-post__footer">
                                            <a
                                                href="{{ $tweet['url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="prensa-post__link"
                                            >
                                                Ver en X
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No se cargó la última publicación de #AlertaLegal.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <hr class="horizontal light mt-0">

        {{-- FAKE NEWS --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner">
                    <span class="section-eyebrow">
                        {{ __('dashboard.fake_news') }}
                    </span>
                    <h2>{{ __('dashboard.fakenews_title') }}</h2>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-4">
                    <div class="article-panel article-panel--profundidad">
                        <div class="article-panel__header">
                            <div class="article-panel__heading">
                                <p class="article-panel__title">
                                    <span class="article-panel__dot"></span>
                                    {{ __('dashboard.en_profundidad') }}
                                </p>
                                <p class="article-panel__subtitle">{{ __('dashboard.enprofundidad_title') }}</p>
                            </div>
                            <span class="article-panel__count">{{ count($postsFakeNewsWeb['en_profundidad']) }}</span>
                        </div>

                        <div class="article-list">
                            @forelse ($postsFakeNewsWeb['en_profundidad'] as $post)
                                <a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="article-card">
                                    @if (!empty($post['imagen']))
                                        <img class="article-card__thumb" src="{{ $post['imagen'] }}" alt="{{ $post['titulo'] }}">
                                    @else
                                        <div class="article-card__thumb article-card__thumb--icon">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                    @endif

                                    <div class="article-card__body">
                                        <p class="article-card__title">{{ $post['titulo'] }}</p>

                                        @if (!empty($post['contenido']))
                                            <p class="article-card__excerpt">
                                                {{ \Illuminate\Support\Str::limit($post['contenido'], 180) }}
                                            </p>
                                        @endif

                                        <div class="article-card__footer">
                                            @if (!empty($post['fecha']))
                                                <span class="article-card__meta">
                                                    <i class="bi bi-calendar3"></i>
                                                    @if (!empty($post['fecha']))
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($post['fecha'])
                                                                ->locale('es')
                                                                ->translatedFormat('d \d\e F \d\e Y') }}
                                                        </small>
                                                    @endif
                                                </span>
                                            @endif

                                            <span class="article-card__action">
                                                {{ __('dashboard.ver_publicacion') }}
                                                <i class="bi bi-chevron-right"></i>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-muted small mb-0">No se pudieron cargar las publicaciones de En profundidad.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="article-panel article-panel--fake">
                        <div class="article-panel__header">
                            <div class="article-panel__heading">
                                <p class="article-panel__title">
                                    <span class="article-panel__dot"></span>
                                    {{ __('dashboard.noti_fake') }}
                                </p>
                                <p class="article-panel__subtitle">{{ __('dashboard.notifake_title') }}</p>
                            </div>
                            <span class="article-panel__count">{{ count($postsFakeNewsWeb['noti_fake']) }}</span>
                        </div>

                        <div class="article-list">
                            @forelse ($postsFakeNewsWeb['noti_fake'] as $post)
                                <a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="article-card">
                                    @if (!empty($post['imagen']))
                                        <img class="article-card__thumb" src="{{ $post['imagen'] }}" alt="{{ $post['titulo'] }}">
                                    @else
                                        <div class="article-card__thumb article-card__thumb--icon">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                    @endif

                                    <div class="article-card__body">
                                        <p class="article-card__title">{{ $post['titulo'] }}</p>

                                        @if (!empty($post['contenido']))
                                            <p class="article-card__excerpt">
                                                {{ \Illuminate\Support\Str::limit($post['contenido'], 180) }}
                                            </p>
                                        @endif

                                        <div class="article-card__footer">
                                            @if (!empty($post['fecha']))
                                                <span class="article-card__meta">
                                                    <i class="bi bi-calendar3"></i>
                                                    @if (!empty($post['fecha']))
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($post['fecha'])
                                                                ->locale('es')
                                                                ->translatedFormat('d \d\e F \d\e Y') }}
                                                        </small>
                                                    @endif
                                                </span>
                                            @endif

                                            <span class="article-card__action">
                                                {{ __('dashboard.ver_publicacion') }}
                                                <i class="bi bi-chevron-right"></i>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-muted small mb-0">No se pudieron cargar las publicaciones de Noti-fake.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-4">                                            
                    <article class="organization-feed h-100">

                        <header class="organization-feed__header">
                            <div class="organization-feed__identity">
                                <div class="organization-feed__logo">
                                    <img
                                        src="{{ $organizations[0]['logo_x'] }}"
                                        alt="{{ $organizations[0]['name'] }}"
                                    >
                                </div>

                                <div class="organization-feed__meta">
                                    <h3>{{ $organizations[0]['name'] }}</h3>

                                    <a
                                        href="https://x.com/{{ $organizations[0]['username'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {{ '@' . $organizations[0]['username'] }}
                                    </a>
                                </div>
                            </div>
                        </header>
                        <div class="organization-feed__posts">
                            @forelse ($postsFakeNewsX as $post)                                
                                <a
                                        href="{{ $post['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="feed-post"
                                    >
                                    <div class="feed-post__image">
                                        @if ($post['image'])
                                            <img
                                                src="{{ $post['image'] }}"
                                                alt=""
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="feed-post__placeholder">
                                                <i class="bi bi-twitter-x"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="feed-post__content">
                                        <p class="feed-post__title">
                                            {{ \Illuminate\Support\Str::limit(
                                                $post['text'],
                                                90
                                            ) }}
                                        </p>

                                        @if ($post['created_at'])
                                            <time class="feed-post__time">
                                                {{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}
                                            </time>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="feed-empty">
                                    <i class="bi bi-info-circle"></i>

                                    <p>
                                        No fue posible cargar las publicaciones
                                        de esta organización.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        <footer class="organization-feed__footer">
                            <a
                                href="https://x.com/{{ $organizations[0]['username'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ __('dashboard.view_on_x') }}

                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </footer>
                    </article>
                </div>
            </div>
        </section>

        <hr class="horizontal light mt-0">

        {{--OBSERVATORIO DE UNIVERSIDADES - OBU --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner">
                    <span class="section-eyebrow">
                        {{ __('dashboard.universities') }}
                    </span>

                    <h2>{{ __('dashboard.university_title') }}</h2>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-6">
                    <div class="chart-panel">
                        <div class="chart-legend mb-2">
                            <span><i class="legend-dot" style="background:var(--navy-2)"></i> {{ __('dashboard.protests') }}</span>
                            <span><i class="legend-dot" style="background:var(--gold)"></i> {{ __('dashboard.complaints') }}</span>
                        </div>
                        <canvas id="protestsComplaintsChart" height="90"></canvas>
                    </div>
                </div>

                <div class="col-6">
                    <div class="chart-panel">
                        <div class="chart-legend mb-2">
                            <span><i class="legend-dot" style="background:var(--blue)"></i> {{ __('dashboard.economic_social_complaints') }}</span>
                            <span><i class="legend-dot" style="background:var(--accent)"></i> {{ __('dashboard.civil_political_complaints') }}</span>
                        </div>
                        <canvas id="complaintTypeByYearChart" height="90"></canvas>
                    </div>
                </div>

                <div class="col-4">
                    <div class="complaint-card">
                        <p class="complaint-card__title">{{ __('dashboard.economic_social_complaints') }}</p>

                        <div class="complaint-card__list">
                            @foreach ($economicSocialItems as $item)
                                <div class="complaint-item">
                                    <div class="complaint-item__icon complaint-item__icon--blue">
                                        <i class="bi {{ $item['icon'] }}"></i>
                                    </div>
                                    <span class="complaint-item__label">{{ $item['label'] }}</span>
                                    <span class="complaint-item__value">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="complaint-card">
                        <p class="complaint-card__title">{{ __('dashboard.civil_political_complaints') }}</p>

                        <div class="complaint-card__list">
                            @foreach ($civilPoliticalItems as $item)
                                <div class="complaint-item">
                                    <div class="complaint-item__icon complaint-item__icon--red">
                                        <i class="bi {{ $item['icon'] }}"></i>
                                    </div>
                                    <span class="complaint-item__label">{{ $item['label'] }}</span>
                                    <span class="complaint-item__value">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="complaint-card">
                        <p class="complaint-card__title">{{ __('dashboard.economic_social_complaints') }}</p>

                        <div class="complaint-card__list">
                            @foreach ($economicSocialItems as $item)
                                <div class="complaint-item">
                                    <div class="complaint-item__icon complaint-item__icon--blue">
                                        <i class="bi {{ $item['icon'] }}"></i>
                                    </div>
                                    <span class="complaint-item__label">{{ $item['label'] }}</span>
                                    <span class="complaint-item__value">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
        </section>

        <hr class="horizontal light mt-0">

         

        {{-- PUBLICACIONES DE X --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner">
                    <span class="section-eyebrow">
                        {{ __('dashboard.social_media') }}
                    </span>

                    <h2>{{ __('dashboard.latest_org_posts') }}</h2>
                </div>
            </div>

            <div class="row g-3">
                @foreach ($organizations as $organization)
                    <div class="col-6 col-xl-3">
                        <article class="organization-feed h-100">
                            <header class="organization-feed__header">
                                <div class="organization-feed__identity">
                                    <div class="organization-feed__logo">
                                        <img
                                            src="{{ $organization['logo_x'] }}"
                                            alt="{{ $organization['name'] }}"
                                        >
                                    </div>

                                    <div class="organization-feed__meta">
                                        <h3>{{ $organization['name'] }}</h3>

                                        <a
                                            href="https://x.com/{{ $organization['username'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {{ '@' . $organization['username'] }}
                                        </a>
                                    </div>
                                </div>
                            </header>

                            <div class="organization-feed__posts">
                                @forelse ($organization['posts']->take(2) as $post)
                                    <a
                                        href="{{ $post['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="feed-post"
                                    >
                                        <div class="feed-post__image">
                                            @if ($post['image'])
                                                <img
                                                    src="{{ $post['image'] }}"
                                                    alt=""
                                                    loading="lazy"
                                                >
                                            @else
                                                <div class="feed-post__placeholder">
                                                    <i class="bi bi-twitter-x"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="feed-post__content">
                                            <p class="feed-post__title">
                                                {{ \Illuminate\Support\Str::limit(
                                                    $post['text'],
                                                    90
                                                ) }}
                                            </p>

                                            @if ($post['created_at'])
                                                <time class="feed-post__time">
                                                    {{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}
                                                </time>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="feed-empty">
                                        <i class="bi bi-info-circle"></i>

                                        <p>
                                            No fue posible cargar las publicaciones
                                            de esta organización.
                                        </p>
                                    </div>
                                @endforelse
                            </div>

                            <footer class="organization-feed__footer">
                                <a
                                    href="https://x.com/{{ $organization['username'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ __('dashboard.view_on_x') }}

                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </footer>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>    
    </main>

    {{-- FOOTER --}}
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

                    <p>{{ __('dashboard.footer_description') }}</p>
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
@endsection