@extends('layouts.public_v2')

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
        $totalVerifications = 28;
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
        @include('dashboard.partials.global-header', ['headerAccent' => '#f2c600'])

        @include('dashboard.organizations.partials.organization-hero', [
            'heroClass' => 'organization-v2-hero--fake-news fake-news-v2-hero',
            'accent' => '#f2c600',
            'accentRgb' => '242, 198, 0',
            'logo' => $organizationLogo,
            'category' => __('dashboard.fake_news_page.badge'),
            'title' => $organizationName,
            'description' => __('dashboard.fake_news_page.description'),
            'illustrationPartial' => 'dashboard.organizations.partials.illustrations.fake-news',
            'lastSyncAt' => $lastSyncAt,
            'timeLabel' => __('dashboard.fake_news_page.venezuela_time'),
        ])

        <section class="fake-news-page-metrics" aria-label="{{ __('dashboard.fake_news_page.metrics_label') }}">
            <div class="jep-page__container fake-news-page-metrics__grid">
                <article class="fake-news-page-metric fake-news-page-metric--total">
                    <span class="fake-news-page-metric--total__icon" aria-hidden="true">
                        <i class="bi bi-patch-check"></i>
                    </span>

                    <div class="fake-news-page-metric--total__copy">
                        <p>{{ __('dashboard.fake_news_page.total_verifications') }}</p>
                    </div>

                    <strong class="fake-news-page-metric--total__value">{{ $totalVerifications }}</strong>

                    <div class="fake-news-page-metric--total__decoration" aria-hidden="true">
                        <span><i class="bi bi-check-lg"></i></span>
                        <span><i class="bi bi-check-lg"></i></span>
                        <span><i class="bi bi-check-lg"></i></span>
                    </div>
                </article>
            </div>
        </section>

        <main class="fake-news-page-content">
            <div class="jep-page__container">
                <header class="fake-news-page-content__header">
                    <div>
                        <span>{{ __('dashboard.fake_news_page.badge') }}</span>
                        <h2>{{ __('dashboard.fake_news_page.editorial_title') }}</h2>
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
        'footerLinks' => [
            'website' => 'https://fakenewsvenezuela.org/',
            'contact' => 'https://fakenewsvenezuela.org/nosotros/contactanos/',
            'info' => 'https://fakenewsvenezuela.org/nosotros/nuestros-valores/',
            'x' => 'https://x.com/observatoriofn',
            'instagram' => 'https://www.instagram.com/Observatoriofn',
        ],
    ])
@endsection
