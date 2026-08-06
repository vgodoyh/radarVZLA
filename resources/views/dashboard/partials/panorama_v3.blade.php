<section class="home-v3-panorama">
    <div class="home-v3-organizations">
        <article class="home-v3-card home-v3-card--jep">
            <header class="home-v3-card__header">
                <img src="{{ asset('assets/img/organizations/jep.svg') }}" alt="Justicia, Encuentro y Perdón" class="home-v3-card__logo home-v3-card__logo--jep">
                <div class="home-v3-card__identity">
                    <h3>Justicia, Encuentro y Perdón</h3>
                    <span>{{ __('dashboard.jep_title') }}</span>
                </div>
            </header>

            <div class="home-v3-metric-grid">
                @foreach ([
                    ['index' => 0, 'icon' => 'bi-people-fill'],
                    ['index' => 1, 'icon' => 'bi-person-standing-dress'],
                    ['index' => 2, 'icon' => 'bi-heart-pulse-fill'],
                    ['index' => 4, 'icon' => 'bi-unlock-fill'],
                ] as $metric)
                    <div class="home-v3-metric">
                        <i class="bi {{ $metric['icon'] }}" aria-hidden="true"></i>
                        <strong>{{ data_get($stats, $metric['index'].'.value', 0) }}</strong>
                        <span>{{ data_get($stats, $metric['index'].'.label', '') }}</span>
                    </div>
                @endforeach
            </div>

            <footer class="home-v3-card__footer">
                <a href="{{ route('organizations.jep') }}">{{ __('dashboard.view_full_dashboard') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </footer>
        </article>

        <article class="home-v3-card home-v3-card--acceso">
            <header class="home-v3-card__header">
                <img src="{{ asset('assets/img/organizations/acceso-justicia.png') }}" alt="Acceso a la Justicia" class="home-v3-card__logo home-v3-card__logo--wide">
                <div class="home-v3-card__identity">
                    <h3>Acceso a la Justicia</h3>
                    <span>{{ __('dashboard.accesojusticia_title') }}</span>
                </div>
            </header>

            <div class="home-v3-posts">
                @forelse (($accesoPosts ?? collect())->take(3) as $post)
                    <a href="{{ $post['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="home-v3-post">
                        <img src="{{ $post['image'] ?? asset('assets/img/placeholders/article.svg') }}" alt="" loading="lazy">
                        <div>
                            <h4>{{ $post['title'] ?? '' }}</h4>
                            <time>{{ $post['date'] ?? '' }}</time>
                        </div>
                    </a>
                @empty
                    <div class="home-v3-empty"><i class="bi bi-inbox" aria-hidden="true"></i> {{ __('dashboard.no_synced_publications') }}</div>
                @endforelse
            </div>

            <footer class="home-v3-card__footer">
                <a href="{{ route('organizations.acceso-justicia') }}">{{ __('dashboard.view_more_publications') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </footer>
        </article>

        <article class="home-v3-card home-v3-card--fake-news">
            <header class="home-v3-card__header">
                <img src="{{ asset('assets/img/organizations/fake-news-a.webp') }}" alt="Observatorio Venezolano de Fake News" class="home-v3-card__logo">
                <div class="home-v3-card__identity">
                    <h3>Observatorio Venezolano de Fake News</h3>
                    <span>{{ __('dashboard.fakenews_title') }}</span>
                </div>
            </header>

            <div class="home-v3-fake-layout">
                <div class="home-v3-fake-metrics">
                    @foreach ([
                        ['icon' => 'bi-patch-check-fill', 'label' => __('dashboard.verifications_published'), 'value' => 24],
                        ['icon' => 'bi-file-earmark-bar-graph-fill', 'label' => __('dashboard.analysis_infographics'), 'value' => 12],
                        ['icon' => 'bi-newspaper', 'label' => __('dashboard.noti_fake_published'), 'value' => 8],
                    ] as $metric)
                        <div>
                            <i class="bi {{ $metric['icon'] }}" aria-hidden="true"></i>
                            <strong>{{ $metric['value'] }}</strong>
                            <span>{{ $metric['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('organizations.fake-news') }}" class="home-v3-featured">
                    <img src="{{ asset('assets/img/organizations/fake-news-x.png') }}" alt="" loading="lazy">
                    <div>
                        <small>{{ __('dashboard.featured_verification') }}</small>
                        <p>Bukele y los terremotos en Venezuela: la imagen falsa que circula en redes</p>
                    </div>
                </a>
            </div>

            <footer class="home-v3-card__footer">
                <a href="{{ route('organizations.fake-news') }}">{{ __('dashboard.view_full_dashboard') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </footer>
        </article>

        <article class="home-v3-card home-v3-card--obu">
            <header class="home-v3-card__header">
                <img src="{{ asset('assets/img/organizations/obu.png') }}" alt="Observatorio de Universidades" class="home-v3-card__logo home-v3-card__logo--wide">
                <div class="home-v3-card__identity">
                    <h3>Observatorio de Universidades</h3>
                    <span>{{ __('dashboard.university_title') }}</span>
                </div>
            </header>

            <div class="home-v3-obu-grid">
                @foreach ([
                    ['icon' => 'bi-file-earmark-bar-graph', 'label' => __('dashboard.obu.total_reports'), 'value' => 222],
                    ['icon' => 'bi-grid-fill', 'label' => __('dashboard.obu.documented_categories'), 'value' => 8],
                    ['icon' => 'bi-megaphone-fill', 'label' => __('dashboard.obu.registered_protests'), 'value' => 75],
                    ['icon' => 'bi-calendar3', 'label' => __('dashboard.obu.analysis_period'), 'value' => __('dashboard.obu.january_june_2026')],
                ] as $metric)
                    <div class="home-v3-obu-metric">
                        <i class="bi {{ $metric['icon'] }}" aria-hidden="true"></i>
                        <strong>{{ $metric['value'] }}</strong>
                        <span>{{ $metric['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <footer class="home-v3-card__footer">
                <a href="{{ route('organizations.universidades') }}">{{ __('dashboard.view_full_dashboard') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </footer>
        </article>
    </div>
</section>
