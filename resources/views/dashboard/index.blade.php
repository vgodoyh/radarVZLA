@extends('layouts.public')

@section('title', __('dashboard.meta_title'))

@section('content')
<header class="hero-section">
    <div class="container-fluid px-4 px-xl-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('dashboard.public') }}" class="brand text-decoration-none">
                <span class="brand-mark"><i class="bi bi-radar"></i></span>
                <span>
                    <strong>Radar VZLA</strong>
                    <small>{{ __('dashboard.public_dashboard') }}</small>
                </span>
            </a>

            <div class="language-switcher" aria-label="{{ __('dashboard.language') }}">
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}">ES</a>
                <span>/</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}">EN</a>
            </div>
        </div>

        <div class="row g-3">
            @foreach (__('dashboard.organizations') as $organization)
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="organization-card h-100">
                        <div class="organization-icon"><i class="{{ $organization['icon'] }}"></i></div>
                        <h2>{{ $organization['name'] }}</h2>
                        <p>{{ $organization['description'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</header>

<main class="container-fluid px-4 px-xl-5 py-4 dashboard-shell">
    <section class="period-bar section-card mb-4">
        <div>
            <span class="section-eyebrow">{{ __('dashboard.fortnight_header') }}</span>
            <div class="d-flex align-items-center gap-3 mt-2">
                <span class="period-icon"><i class="bi bi-calendar3"></i></span>
                <div>
                    <h1>{{ __('dashboard.period') }}</h1>
                    <p>{{ __('dashboard.in_progress') }}</p>
                </div>
            </div>
        </div>
        <button class="btn btn-primary px-4 py-3">
            <i class="bi bi-cloud-arrow-up me-2"></i>{{ __('dashboard.publish_report') }}
        </button>
    </section>

    <section class="mb-4">
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

    <section class="section-card university-section mb-4">
        <div class="section-heading mb-4">
            <div>
                <span class="section-eyebrow">{{ __('dashboard.universities') }}</span>
                <h2>{{ __('dashboard.university_title') }}</h2>
                <p>{{ __('dashboard.university_description') }}</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6"><div class="chart-panel"><canvas id="protestsChart" height="135"></canvas></div></div>
            <div class="col-12 col-xl-6"><div class="chart-panel"><canvas id="complaintsChart" height="135"></canvas></div></div>
            <div class="col-12 col-lg-6"><div class="chart-panel"><canvas id="complaintTypesChart" height="180"></canvas></div></div>
            <div class="col-12 col-lg-6"><div class="chart-panel"><canvas id="topicsChart" height="180"></canvas></div></div>
        </div>
    </section>

    <section class="mb-5">
        <div class="section-heading">
            <div>
                <span class="section-eyebrow">{{ __('dashboard.latest_posts') }}</span>
                <h2>{{ __('dashboard.from_x') }}</h2>
            </div>
        </div>

        <div class="row g-3">
            @foreach (__('dashboard.posts') as $post)
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="post-card h-100">
                        <div class="post-header">
                            <span class="avatar"><i class="{{ $post['icon'] }}"></i></span>
                            <div><strong>{{ $post['name'] }}</strong><small>{{ $post['handle'] }}</small></div>
                            <i class="bi bi-twitter-x ms-auto"></i>
                        </div>
                        <p>{{ $post['text'] }}</p>
                        <a href="#">{{ __('dashboard.view_on_x') }} <i class="bi bi-arrow-up-right"></i></a>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
</main>
@endsection
