@extends('layouts.public')

@section('title', __('dashboard.meta_title'))

@section('content')
    <header class="hero-section">
        <div class="container-fluid px-4 px-xl-5 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <a href="{{ route('dashboard.public') }}" class="brand text-decoration-none">
                    <img
                        src="{{ asset('assets/img/logos/radar-vzla.png') }}"
                        alt="Radar Vzla" class="w-25"
                    >
                </a>

                <div class="d-flex align-items-center gap-3">
                    <div class="datetime text-end text-white-50" id="rv-datetime">
                        <div class="fw-semibold text-white" id="rv-time"></div>
                        <div class="small text-uppercase-first" id="rv-date"></div>
                    </div>

                    <div class="language-switcher" aria-label="{{ __('dashboard.language') }}">
                        <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}">ES</a>
                        <span>/</span>
                        <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}">EN</a>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                @foreach (__('dashboard.organizations') as $organization)
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="{{ $organization['url'] }}"
                            target="_blank" style="text-decoration: none;">
                            <article class="organization-card h-100">                        
                                <div class="organization-logo">
                                    <img
                                        src="{{ $organization['logo'] }}"
                                        alt="{{ $organization['name'] }}"
                                        class="img-fluid">
                                </div>
                                <h2 class="text-white">{{ $organization['name'] }}</h2>
                                <p>{{ $organization['description'] }}</p>
                            </article>
                        </a>
                    </div>
                @endforeach
            </div>
            <script>
                function rvUpdateClock() {
                    const now = new Date();
                    const locale = document.documentElement.lang === 'en' ? 'en-US' : 'es-VE';
                    document.getElementById('rv-time').textContent =
                        now.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
                    let dateStr = now.toLocaleDateString(locale, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    document.getElementById('rv-date').textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
                }
                rvUpdateClock();
                setInterval(rvUpdateClock, 30000);
            </script>
        </div>
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
                
            </div>

        </section>

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

        {{-- JEP --}}
        <section class="mb-5" style="margin-top:50px;">
            <div class="section-heading mb-4">
                <div class="section-heading__inner">
                    <span class="section-eyebrow">
                        {{ __('dashboard.jep') }}
                    </span>

                    <h2>{{ __('dashboard.jep_title') }}</h2>
                </div>
            </div>
        </section>

        <section class="mb-4" style="margin-top:50px;">
            <div class="section-heading">
                <div>
                    <span class="section-eyebrow">{{ __('dashboard.key_figures') }}</span>
                    <h2>{{ __('dashboard.snapshot') }}</h2>
                </div>
                <small>{{ __('dashboard.comparison') }}</small>
            </div>

            <div class="row g-3">
                @foreach ($stats as $stat)
                    <div class="col-12 col-sm-6 col-xl">
                        <article class="metric-card h-100">
                            <div class="metric-top">
                                <span class="metric-icon"><i class="bi {{ $stat['icon'] }}"></i></span>
                                <span>{{ $stat['label'] }}</span>
                            </div>
                            <strong>{{ $stat['value'] }}</strong>
                            <small class="{{ $stat['trend'] === 'up-danger' ? 'text-danger' : 'text-success' }}">
                                <i class="bi bi-arrow-up"></i> {{ $stat['change'] }}
                            </small>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section-card featured-section mb-4">
            <div class="section-heading mb-4">
                <div>
                    <span class="section-eyebrow">{{ __('dashboard.featured_indicator') }}</span>
                    <h2>{{ __('dashboard.featured_title') }}</h2>
                </div>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-lg-7">
                    <div class="chart-panel h-100">
                        <canvas id="featuredChart" height="120"></canvas>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="analysis-panel h-100">
                        <p>{{ __('dashboard.featured_analysis') }}</p>
                        <div class="slim-links">
                            <a href="#"><i class="bi bi-file-earmark-text"></i>{{ __('dashboard.press_release') }}<i class="bi bi-arrow-up-right"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i>{{ __('dashboard.x_thread') }}<i class="bi bi-arrow-up-right"></i></a>
                            <a href="#"><i class="bi bi-globe2"></i>{{ __('dashboard.full_website') }}<i class="bi bi-arrow-up-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="section-heading">
                <div>
                    <span class="section-eyebrow">{{ __('dashboard.indicator_groups') }}</span>
                    <h2>{{ __('dashboard.explore_data') }}</h2>
                </div>
            </div>

            <div class="row g-3">
                @foreach (__('dashboard.groups') as $group)
                    <div class="col-12 col-md-6 col-xl-3">
                        <article class="indicator-card h-100">
                            <div class="indicator-icon"><i class="{{ $group['icon'] }}"></i></div>
                            <h3>{{ $group['title'] }}</h3>
                            <ul>
                                @foreach ($group['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <a href="#">{{ __('dashboard.view_indicators') }} <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

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
                            <span><i class="legend-dot" style="background:#0b3769"></i> {{ __('dashboard.protests') }}</span>
                            <span><i class="legend-dot" style="background:#FFD23F"></i> {{ __('dashboard.complaints') }}</span>
                        </div>
                        <canvas id="protestsComplaintsChart" height="90"></canvas>
                    </div>
                </div>

                <div class="col-6">
                    <div class="chart-panel">
                        <div class="chart-legend mb-2">
                            <span><i class="legend-dot" style="background:#1f66d1"></i> {{ __('dashboard.economic_social_complaints') }}</span>
                            <span><i class="legend-dot" style="background:#00B89C"></i> {{ __('dashboard.civil_political_complaints') }}</span>
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
                            src="{{ asset('assets/img/logos/radar-vzla.png') }}"
                            alt="Radar Venezuela"
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
                        <a href="https://accesoalajusticia.org" target="_blank" rel="noopener noreferrer">
                            Acceso a la Justicia
                        </a>
                        <a href="https://fakenewsvenezuela.org" target="_blank" rel="noopener noreferrer">
                            Observatorio Fake News
                        </a>
                        <a href="https://jepvenezuela.com" target="_blank" rel="noopener noreferrer">
                            JEP Venezuela
                        </a>
                        <a href="https://observatoriodeuniversidades.com" target="_blank" rel="noopener noreferrer">
                            Observatorio de Universidades
                        </a>
                    </div>
                </div>

                <div class="site-footer__col">
                    <div class="site-footer__status">
                        <span class="site-footer__status-dot"></span>
                        <span>{{ __('dashboard.data_updated') }}</span>
                    </div>

                    <p class="site-footer__sync">
                        {{ __('dashboard.last_sync') }}<br>
                        {{ $lastSync ?? now()->format('d/m/Y, H:i') }}
                    </p>
                </div>

            </div>

            <div class="site-footer__bottom">
                <p>
                    &copy; {{ date('Y') }} Radar Venezuela.
                    {{ __('dashboard.footer_disclaimer') }}
                </p>
            </div>
        </div>
    </footer>
@endsection
