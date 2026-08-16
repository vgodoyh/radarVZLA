@extends('layouts.public')

@section('title', __('dashboard.fake_news_page.meta_title'))

@section('content')
    @php
        $fakeNewsOrganization = collect($organizations ?? [])->firstWhere('slug', 'fake-news') ?? ($organization ?? []);
        $deepPosts = collect(data_get($postsFakeNewsWeb ?? [], 'en_profundidad', []));
        $notiFakePosts = collect(data_get($postsFakeNewsWeb ?? [], 'noti_fake', []));
        $xPosts = collect($postsFakeNewsX ?? []);
        $featuredDeepPost = $deepPosts->first();
        $secondaryDeepPosts = $deepPosts->skip(1)->take(3);
        $magazineNotiFakePosts = $notiFakePosts->take(4);
        $magazineXPosts = $xPosts->take(7);
        $verifiedCount = $deepPosts->count() + $notiFakePosts->count();
        $publicationTotal = $verifiedCount + $xPosts->count();
        $organizationName = data_get($fakeNewsOrganization, 'name', __('dashboard.fake_news_page.organization'));
        $organizationUsername = data_get($fakeNewsOrganization, 'username');
        $organizationWebsite = data_get($fakeNewsOrganization, 'website_url') ?? data_get($fakeNewsOrganization, 'url');
        $organizationLogo = data_get($fakeNewsOrganization, 'logo_x') ?? data_get($fakeNewsOrganization, 'logo');
        $xProfileUrl = filled($organizationUsername) ? 'https://x.com/'.$organizationUsername : null;
        $lastSyncAt = filled($lastSync ?? null)
            ? rescue(
                fn () => \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale()),
                null,
                report: false
            )
            : null;
        $localizedDate = static fn ($value) => filled($value)
            ? rescue(
                fn () => \Carbon\Carbon::parse($value)->locale(app()->getLocale())->translatedFormat('d M Y'),
                null,
                report: false
            )
            : null;
        $relativeDate = static fn ($value) => filled($value)
            ? rescue(
                fn () => \Carbon\Carbon::parse($value)->locale(app()->getLocale())->diffForHumans(),
                null,
                report: false
            )
            : null;
    @endphp

    <div class="fake-news-page">
        <header class="jep-page__hero hero-section--light fake-news-page__hero">
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

                        <div class="jep-page__identity jep-page__identity--fake-news">
                            <span class="jep-page__logo">
                                @if (filled($organizationLogo))
                                    <img src="{{ $organizationLogo }}" alt="{{ $organizationName }}">
                                @else
                                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                                @endif
                            </span>

                            <div>
                                <span class="jep-page__eyebrow">{{ __('dashboard.fake_news_page.badge') }}</span>
                                <h1>{{ $organizationName }}</h1>
                                <p class="jep-page__description">{{ __('dashboard.fake_news_page.description') }}</p>

                                <div class="jep-page__links">
                                    @if ($xProfileUrl)
                                        <a href="{{ $xProfileUrl }}" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-twitter-x" aria-hidden="true"></i>
                                            {{ '@'.$organizationUsername }}
                                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                        </a>
                                    @endif

                                    @if (filled($organizationWebsite))
                                        <a href="{{ $organizationWebsite }}" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-globe2" aria-hidden="true"></i>
                                            {{ preg_replace('#^https?://(www\.)?#', '', rtrim($organizationWebsite, '/')) }}
                                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @include('dashboard.organizations.partials.mobile-update', [
                            'lastSyncAt' => $lastSyncAt,
                            'accent' => '#f2c600',
                        ])
                    </div>

                    <div class="jep-page__visual">
                        <div class="jep-page__map" aria-hidden="true">
                            <img src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}" alt="">
                        </div>

                        <div class="hero-update-card jep-page__update-card fake-news-page__update-card">
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
                                    <span>{{ $lastSyncAt->format('H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="fake-news-page-metrics" aria-label="{{ __('dashboard.fake_news_page.metrics_label') }}">
            <div class="jep-page__container fake-news-page-metrics__grid">
                @foreach ([
                    ['icon' => 'bi-patch-check', 'value' => $verifiedCount, 'label' => __('dashboard.fake_news_page.verified_publications')],
                    ['icon' => 'bi-file-earmark-bar-graph', 'value' => $deepPosts->count(), 'label' => __('dashboard.analysis_infographics')],
                    ['icon' => 'bi-newspaper', 'value' => $notiFakePosts->count(), 'label' => __('dashboard.noti_fake_published')],
                    ['icon' => 'bi-twitter-x', 'value' => $xPosts->count(), 'label' => __('dashboard.fake_news_page.x_publications')],
                ] as $metric)
                    <article class="fake-news-page-metric">
                        <span><i class="bi {{ $metric['icon'] }}" aria-hidden="true"></i></span>
                        <div>
                            <strong>{{ $metric['value'] }}</strong>
                            <p>{{ $metric['label'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <main class="fake-news-page-content">
            <div class="jep-page__container">
                <header class="fake-news-page-content__header">
                    <div>
                        <span>{{ $organizationName }}</span>
                        <h2>{{ __('dashboard.fake_news_page.content_title') }}</h2>
                        <p>{{ __('dashboard.fake_news_page.content_intro') }}</p>
                    </div>
                    <small>{{ trans_choice('dashboard.publication_count', $publicationTotal, ['count' => $publicationTotal]) }}</small>
                </header>

                <div class="fake-news-magazine-layout">
                    @include('dashboard.organizations.fake-news.magazine-deep')
                    @include('dashboard.organizations.fake-news.magazine-noti-fake')
                </div>

                @include('dashboard.organizations.fake-news.x-horizontal-feed')

                <aside class="fake-news-methodology">
                    <span aria-hidden="true"><i class="bi bi-info-circle"></i></span>
                    <div>
                        <h2>{{ __('dashboard.methodology_note_title') }}</h2>
                        <p>{{ __('dashboard.fake_news_page.methodology') }}</p>
                        @if (filled($organizationWebsite))
                            <a href="{{ $organizationWebsite }}" target="_blank" rel="noopener noreferrer">
                                {{ __('dashboard.fake_news_page.about_observatory') }}
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </aside>
            </div>
        </main>
    </div>

    @include('dashboard.partials.organization-footer', [
        'footerOrganization' => $fakeNewsOrganization,
        'footerCategory' => __('dashboard.fake_news_page.badge'),
        'footerAccent' => '#f2c600',
    ])
@endsection
