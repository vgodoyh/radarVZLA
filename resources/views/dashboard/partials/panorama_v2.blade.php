 <section class="home-panorama">

    <div class="panorama-heading">

        <div class="panorama-heading__title-row">
            <span class="panorama-heading__line" aria-hidden="true"></span>
            <h2 class="panorama-heading__title">
                {{ __('dashboard.general_overview') }}
            </h2>
            <span class="panorama-heading__line" aria-hidden="true"></span>
        </div>

        <p class="panorama-heading__subtitle">
            {{ __('dashboard.general_overview_description') }}
        </p>
    </div>

    <div class="home-panorama__list">

        {{-- =====================================================
             JUSTICIA, ENCUENTRO Y PERDÓN
        ====================================================== --}}
        @php
            $jepFeaturedStat = data_get($stats ?? [], 0);
            $jepSecondaryStats = collect($stats ?? [])->slice(1, 4);
        @endphp

        <div class="panorama-jep">
            <article class="panorama-jep__main">
                <header class="panorama-jep__header">
                    <div class="panorama-jep__identity">
                        <img
                            src="{{ asset('assets/img/organizations/jep.svg') }}"
                            alt=""
                            class="panorama-jep__isotype"
                        >
                        <span class="panorama-jep__organization">{{ __('dashboard.jep') }}</span>
                    </div>

                    <span class="panorama-jep__category">{{ __('dashboard.jep_title') }}</span>
                </header>

                <div class="panorama-jep__featured">
                    <p class="panorama-jep__featured-label">
                        {{ data_get($jepFeaturedStat, 'label', __('dashboard.stats.political_prisoners')) }}
                    </p>

                    <div class="panorama-jep__featured-data">
                        <strong class="panorama-jep__featured-value">
                            {{ data_get($jepFeaturedStat, 'value', '—') }}
                        </strong>

                        @if (filled(data_get($jepFeaturedStat, 'change')))
                            <div class="panorama-jep__variation panorama-jep__variation--{{ data_get($jepFeaturedStat, 'sentiment') === 'positive' ? 'positive' : 'negative' }}">
                                <span>
                                    <i class="bi bi-arrow-{{ data_get($jepFeaturedStat, 'direction') === 'down' ? 'down' : 'up' }}-short" aria-hidden="true"></i>
                                    {{ data_get($jepFeaturedStat, 'change') }}
                                </span>
                                <small>{{ __('dashboard.previous_month_comparison') }}</small>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="panorama-jep__secondary-stats">
                    @foreach ($jepSecondaryStats as $jepStat)
                        <div class="panorama-jep__stat">
                            <span class="panorama-jep__stat-icon" aria-hidden="true">
                                <i class="bi {{ data_get($jepStat, 'icon') }}"></i>
                            </span>
                            <strong>{{ data_get($jepStat, 'value', '—') }}</strong>
                            <span class="panorama-jep__stat-label">{{ data_get($jepStat, 'label') }}</span>

                            @if (filled(data_get($jepStat, 'change')))
                                <small class="panorama-jep__stat-change panorama-jep__stat-change--{{ data_get($jepStat, 'sentiment') === 'positive' ? 'positive' : 'negative' }}">
                                    <i class="bi bi-arrow-{{ data_get($jepStat, 'direction') === 'down' ? 'down' : 'up' }}-short" aria-hidden="true"></i>
                                    {{ data_get($jepStat, 'change') }}
                                </small>
                            @endif
                        </div>
                    @endforeach
                </div>

                <footer class="panorama-jep__footer">
                    <span>
                        
                    </span>

                    <a href="{{ route('organizations.jep') }}">
                        {{ __('dashboard.view_full_dashboard') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </footer>
            </article>

            <aside class="panorama-jep__alert">
                <div class="panorama-jep__alert-header">
                    <span class="panorama-jep__alert-icon" aria-hidden="true">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <h3>{{ __('dashboard.jep_page.indicators.monthly_alert') }}</h3>
                </div>
                <p>{{ __('dashboard.jep_page.indicators.alert_text') }}</p>
                <a href="{{ route('organizations.jep') }}">
                    {{ __('dashboard.jep_page.indicators.view_alert') }}
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
                <img
                    src="{{ asset('assets/img/barrotes.png') }}"
                    class="panorama-jep__alert-decoration"
                    alt=""
                    aria-hidden="true"
                >
            </aside>
        </div>

        @php
            $accessPosts = collect($accesoPosts ?? [])->take(5)->values();
            $accessFeatured = $accessPosts->first();
            $accessSecondaryPosts = $accessPosts->skip(1)->take(4)->values();

            $fakeDeepPosts = collect(data_get($postsFakeNewsWeb ?? [], 'en_profundidad', []));
            $fakeNotiPosts = collect(data_get($postsFakeNewsWeb ?? [], 'noti_fake', []));
            $fakeXPosts = collect($postsFakeNewsX ?? []);
            $fakeFeatured = $fakeNotiPosts->first() ?? $fakeDeepPosts->first() ?? $fakeXPosts->first();
            $fakeFeaturedTitle = data_get($fakeFeatured, 'titulo') ?? data_get($fakeFeatured, 'text');
            $fakeFeaturedImage = data_get($fakeFeatured, 'imagen') ?? data_get($fakeFeatured, 'image');
            $fakeFeaturedDateValue = data_get($fakeFeatured, 'fecha') ?? data_get($fakeFeatured, 'created_at');
            $fakeFeaturedDate = filled($fakeFeaturedDateValue)
                ? rescue(fn () => \Carbon\Carbon::parse($fakeFeaturedDateValue)->locale(app()->getLocale())->translatedFormat('d M Y'), '', false)
                : '';
            $obuItems = collect($economicSocialItems ?? [])->concat($civilPoliticalItems ?? []);
            $obuYears = collect($years ?? [])->filter()->values();
            $obuMetrics = [
                ['value' => $obuItems->isNotEmpty() ? $obuItems->sum('value') : null, 'label' => __('dashboard.dashboard_v2.university_monitoring')],
                ['value' => $obuItems->isNotEmpty() ? $obuItems->count() : null, 'label' => __('dashboard.protests')],
                ['value' => collect($protestsData ?? [])->isNotEmpty() ? collect($protestsData)->sum() : null, 'label' => __('dashboard.complaints')],
                ['value' => $obuYears->isNotEmpty() ? $obuYears->first().' – '.$obuYears->last() : null, 'label' => __('dashboard.obu.analysis_period'), 'period' => true],
            ];
            $obuPeriod = $obuYears->isNotEmpty() ? $obuYears->first().' – '.$obuYears->last() : null;
        @endphp

        <div class="panorama-secondary-grid">
            <article class="panorama-secondary-card panorama-access">
                <header class="panorama-secondary-card__header">
                    <img src="{{ asset('assets/img/organizations/acceso-justicia-.png') }}" alt="Acceso a la Justicia" class="panorama-access__logo">
                    <div>
                        <h3>{{ __('dashboard.acceso_justicia') }}</h3>
                        <p>{{ __('dashboard.accesojusticia_title') }}</p>
                    </div>
                </header>

                <p class="panorama-secondary-card__eyebrow">{{ __('dashboard.latest_content') }}</p>

                <div class="panorama-access__content">
                    @if ($accessFeatured)
                        <a href="{{ data_get($accessFeatured, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="panorama-access__featured panorama-access__featured-link">
                            <img src="{{ data_get($accessFeatured, 'image') ?: asset('assets/img/placeholders/article.svg') }}" alt="{{ data_get($accessFeatured, 'title', '') }}">
                            <div>
                                <span class="panorama-access__featured-category">{{ __('dashboard.legal_alert_badge') }}</span>
                                <span class="panorama-access__featured-external" aria-hidden="true">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </span>
                                <h4 class="panorama-access__featured-title">{{ data_get($accessFeatured, 'title') }}</h4>
                                <time>{{ data_get($accessFeatured, 'date') }}</time>
                            </div>
                        </a>

                        @foreach ($accessSecondaryPosts as $accessSecondary)
                            <a href="{{ data_get($accessSecondary, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="panorama-access__secondary">
                                <time class="panorama-access__secondary-date">{{ data_get($accessSecondary, 'date') }}</time>
                                <span class="panorama-access__secondary-text">{{ data_get($accessSecondary, 'title') }}</span>
                                <span class="panorama-access__external-icon" aria-hidden="true">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </span>
                            </a>
                        @endforeach
                    @else
                        <p class="panorama-secondary-card__empty">{{ __('dashboard.no_synced_publications') }}</p>
                    @endif
                </div>

                <footer class="panorama-secondary-card__footer">
                    <span></span>
                    <a href="{{ route('organizations.acceso-justicia') }}">{{ __('dashboard.view_more_publications') }}<i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </footer>
            </article>

            <article class="panorama-secondary-card panorama-fake-news">
                <header class="panorama-secondary-card__header">
                    <img src="{{ asset('assets/img/organizations/fake-news-x.png') }}" alt="Observatorio Venezolano de Fake News" class="panorama-fake-news__logo">
                    <div>
                        <h3>{{ __('dashboard.fake_news') }}</h3>
                        <p>{{ __('dashboard.fakenews_title') }}</p>
                    </div>
                </header>

                {{-- Datos temporales hasta implementar la carga desde el panel administrativo --}}
                @php
                    $socialNetworks = collect($fakeNewsSocialNetworks ?? [
                        ['key' => 'tiktok', 'name' => __('dashboard.social_networks.tiktok'), 'total' => 48, 'percentage' => 36.1, 'icon' => 'fa-brands fa-tiktok'],
                        ['key' => 'whatsaap', 'name' => __('dashboard.social_networks.whatsaap'), 'total' => 41, 'percentage' => 30.8, 'icon' => 'fa-brands fa-whatsapp'],
                        ['key' => 'x', 'name' => __('dashboard.social_networks.x'), 'total' => 17, 'percentage' => 12.8, 'icon' => 'fa-brands fa-x-twitter'],
                        ['key' => 'instagram', 'name' => __('dashboard.social_networks.instagram'), 'total' => 16, 'percentage' => 12, 'icon' => 'fa-brands fa-instagram'],
                        ['key' => 'facebook', 'name' => __('dashboard.social_networks.facebook'), 'total' => 11, 'percentage' => 8.3, 'icon' => 'fa-brands fa-facebook-f'],
                    ]);
                @endphp

                <div class="panorama-ovfn__circulation">
                    <div class="panorama-ovfn__section-header">
                        <h4>{{ __('dashboard.where_it_spreads') }}</h4>
                        <div class="panorama-ovfn__period">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <span>{{ __('dashboard.disinformation_since') }}</span>
                        </div>
                    </div>

                    <div class="panorama-ovfn__networks">
                        @foreach ($socialNetworks as $network)
                            @if (filled(data_get($network, 'total')) && filled(data_get($network, 'percentage')))
                                @php
                                    $networkPercentage = max(0, min(100, (float) data_get($network, 'percentage')));
                                @endphp
                                <div class="panorama-ovfn__network">
                                    <div class="panorama-ovfn__network-name">
                                        <span class="panorama-ovfn__network-icon" aria-hidden="true">
                                            <i class="{{ data_get($network, 'icon') }}"></i>
                                        </span>
                                        <span>{{ data_get($network, 'name') }}</span>
                                    </div>
                                    <div class="panorama-ovfn__network-bar" role="img" aria-label="{{ data_get($network, 'name') }}: {{ $networkPercentage }}%">
                                        <span class="panorama-ovfn__network-progress" style="width: {{ $networkPercentage }}%"></span>
                                    </div>
                                    <strong class="panorama-ovfn__network-count">{{ number_format((int) data_get($network, 'total'), 0, ',', '.') }}</strong>
                                    <span class="panorama-ovfn__network-percentage">({{ number_format($networkPercentage, 1) }}%)</span>
                                </div>
                            @endif
                        @endforeach

                        @if ($socialNetworks->isEmpty())
                            <p class="panorama-secondary-card__empty">{{ __('dashboard.social_networks.unavailable') }}</p>
                        @endif
                    </div>
                </div>

                @if ($fakeFeatured && filled($fakeFeaturedTitle))
                    <div class="panorama-fake-news__featured-wrap">
                        <p>{{ __('dashboard.featured_verification') }}</p>
                        <a href="{{ data_get($fakeFeatured, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="panorama-fake-news__featured">
                            <img src="{{ $fakeFeaturedImage ?: asset('assets/img/placeholders/article.svg') }}" alt="{{ $fakeFeaturedTitle }}">
                            <div>
                                <span>{{ data_get($fakeFeatured, 'seccion_nombre', __('dashboard.noti_fake')) }}</span>
                                <h4>{{ $fakeFeaturedTitle }}</h4>
                                @if ($fakeFeaturedDate)
                                    <time>{{ $fakeFeaturedDate }}</time>
                                @endif
                            </div>
                        </a>
                    </div>
                @endif

                <footer class="panorama-secondary-card__footer">
                    <span></span>
                    <a href="{{ route('organizations.fake-news') }}">{{ __('dashboard.view_full_dashboard') }}<i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </footer>
            </article>

            <article class="panorama-secondary-card panorama-obu">
                <header class="panorama-secondary-card__header">
                    <img src="{{ asset('assets/img/organizations/obu.png') }}" alt="Observatorio de Universidades" class="panorama-obu__logo">
                    <div>
                        <h3>{{ __('dashboard.universities') }}</h3>
                        <p>{{ __('dashboard.university_title') }}</p>
                    </div>
                </header>

                <p class="panorama-secondary-card__eyebrow">{{ __('dashboard.panorama_secondary.universities_in_figures') }}</p>

                <div class="panorama-obu__metrics">
                    @foreach (collect($obuMetrics)->take(3) as $obuMetric)
                        @if (filled($obuMetric['value']))
                            <div class="panorama-obu__metric">
                                <strong class="{{ ! empty($obuMetric['period']) ? 'panorama-obu__metric-value--period' : '' }}">{{ $obuMetric['value'] }}</strong>
                                <span>{{ $obuMetric['label'] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Datos temporales hasta implementar la carga desde el panel administrativo --}}
                @php
                    $obuCategories = collect($obuDocumentedCategories ?? [
                        ['name' => __('dashboard.obu.panorama_categories.student_rights'), 'total' => 142, 'icon' => 'bi-people'],
                        ['name' => __('dashboard.obu.panorama_categories.university_autonomy'), 'total' => 98, 'icon' => 'bi-mortarboard'],
                        ['name' => __('dashboard.obu.panorama_categories.working_conditions'), 'total' => 87, 'icon' => 'bi-briefcase'],
                        ['name' => __('dashboard.obu.panorama_categories.funding'), 'total' => 76, 'icon' => 'bi-currency-dollar'],
                        ['name' => __('dashboard.obu.panorama_categories.repression_security'), 'total' => 68, 'icon' => 'bi-shield'],
                        ['name' => __('dashboard.obu.panorama_categories.other'), 'total' => 92, 'icon' => 'bi-three-dots'],
                    ]);
                @endphp

                <section class="panorama-obu__categories-section" aria-labelledby="panorama-obu-categories-title">
                    <h4 id="panorama-obu-categories-title">{{ __('dashboard.obu.types_of_rights') }}</h4>

                    <div class="panorama-obu__categories">
                        @foreach ($obuCategories as $category)
                            <div class="panorama-obu__category">
                                <div class="panorama-obu__category-main">
                                    <span class="panorama-obu__category-icon" aria-hidden="true">
                                        <i class="bi {{ data_get($category, 'icon') }}"></i>
                                    </span>
                                    <span class="panorama-obu__category-name">{{ data_get($category, 'name') }}</span>
                                </div>
                                <strong class="panorama-obu__category-total">{{ number_format((int) data_get($category, 'total'), 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if (filled($obuPeriod))
                    <p class="panorama-obu__period-note">
                        {{ __('dashboard.obu.accumulated_period', ['period' => $obuPeriod]) }}
                    </p>
                @endif

                <footer class="panorama-secondary-card__footer">
                    <span></span>
                    <a href="{{ route('organizations.universidades') }}">{{ __('dashboard.view_full_dashboard') }}<i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </footer>
            </article>
        </div>

    </div>
</section>
