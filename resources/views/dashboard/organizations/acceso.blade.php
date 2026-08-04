@extends('layouts.public')

@section('title', __('dashboard.acceso_page_title'))

@section('content')
    <div class="access-justice-detail">
        @include('dashboard.organizations.partials.hero', [
            'sectionLabel' => '#AlertaLegal',
            'theme' => 'acceso',
            'showStats' => false,
            'searchable' => true,
        ])

        @php
            $syncedPublications = method_exists($posts, 'total') ? $posts->total() : $posts->count();
            $legalAlerts = collect($alertasLegales ?? []);
            $caracasNow = now('America/Caracas');
            $monthlyPublications = $legalAlerts->filter(function (array $post) use ($caracasNow): bool {
                if (blank($post['created_at'] ?? null)) {
                    return false;
                }

                return \Carbon\Carbon::parse($post['created_at'])
                    ->setTimezone('America/Caracas')
                    ->isSameMonth($caracasNow);
            })->count();
            $latestPublicationAt = data_get($legalAlerts->first(), 'created_at');
        @endphp

        <section class="access-justice-detail__kpis" aria-label="{{ __('dashboard.acceso_kpi_summary') }}">
            <div class="access-justice-detail__container access-justice-detail__kpi-grid">
                <article class="hero-kpi-card access-justice-detail__kpi-card">
                    <span class="hero-kpi-card__icon hero-kpi-card__icon--orange">
                        <i class="bi bi-database-check" aria-hidden="true"></i>
                    </span>
                    <div>
                        <strong class="hero-kpi-card__value">{{ $syncedPublications }}</strong>
                        <span class="hero-kpi-card__label">{{ __('dashboard.synced_publications') }}</span>
                    </div>
                </article>

                <article class="hero-kpi-card access-justice-detail__kpi-card">
                    <span class="hero-kpi-card__icon hero-kpi-card__icon--blue">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                    </span>
                    <div>
                        <strong class="hero-kpi-card__value">{{ $monthlyPublications }}</strong>
                        <span class="hero-kpi-card__label">{{ __('dashboard.publications_this_month') }}</span>
                    </div>
                </article>

                <article class="hero-kpi-card access-justice-detail__kpi-card">
                    <span class="hero-kpi-card__icon hero-kpi-card__icon--cyan">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                    </span>
                    <div>
                        <strong class="hero-kpi-card__value access-justice-detail__kpi-date">
                            {{ $latestPublicationAt
                                ? \Carbon\Carbon::parse($latestPublicationAt)->locale(app()->getLocale())->diffForHumans()
                                : __('dashboard.pending_sync') }}
                        </strong>
                        <span class="hero-kpi-card__label">{{ __('dashboard.latest_publication') }}</span>
                    </div>
                </article>
            </div>
        </section>

        <main class="access-justice-detail__main">
            <div class="access-justice-detail__container">
                <section class="access-justice-detail__publications">
                    <header class="access-justice-detail__section-header">
                        <div class="access-justice-detail__section-title">
                            <span aria-hidden="true"></span>
                            <div>
                                <p>#AlertaLegal</p>
                                <h2>{{ __('dashboard.latest_content') }}</h2>
                            </div>
                        </div>

                        @php($publicationCount = method_exists($posts, 'total') ? $posts->total() : $posts->count())
                        <span class="access-justice-detail__count">
                            {{ trans_choice('dashboard.publication_count', $publicationCount, ['count' => $publicationCount]) }}
                        </span>
                    </header>

                    <form
                        action="{{ route('organizations.acceso-justicia') }}"
                        method="GET"
                        class="access-justice-detail__search"
                        role="search"
                    >
                        <div class="access-justice-detail__search-field">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input
                                type="search"
                                name="q"
                                value="{{ $search }}"
                                placeholder="{{ __('dashboard.search_legal_alerts') }}"
                                aria-label="{{ __('dashboard.search_publications') }}"
                            >
                        </div>

                        <button type="submit">{{ __('dashboard.search_button') }}</button>

                        @if (filled($search))
                            <a href="{{ route('organizations.acceso-justicia') }}">
                                {{ __('dashboard.clear_search') }}
                            </a>
                        @endif
                    </form>

                    <div class="access-justice-detail__grid">
                        @forelse ($posts as $post)
                            @include('dashboard.organizations.partials.publication-card', ['post' => $post])
                        @empty
                            <div class="access-justice-detail__empty">
                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                <p>{{ __('dashboard.no_synced_publications') }}</p>
                            </div>
                        @endforelse
                    </div>

                    @if (method_exists($posts, 'hasPages') && $posts->hasPages())
                        <div class="access-justice-detail__pagination">
                            {{ $posts->links('dashboard.partials.pagination') }}
                        </div>
                    @endif

                    @include('dashboard.organizations.partials.methodology-note')
                </section>
            </div>
        </main>
    </div>

    @include('dashboard.partials.footer', [
        'lastSync' => $lastSync ?? null,
    ])
@endsection
