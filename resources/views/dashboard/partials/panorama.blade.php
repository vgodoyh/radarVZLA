 <section class="home-panorama">

    {{-- Encabezado --}}
    <div class="home-panorama__heading">
        <div class="home-panorama__title">
            <span class="home-panorama__dot"></span>

            <div>
                <h2>{{ __('dashboard.general_overview') }}</h2>

                <p>
                    {{ __('dashboard.general_overview_description') }}
                </p>
            </div>
        </div>
    </div>

    <div class="home-panorama__list">

        {{-- =====================================================
             JUSTICIA, ENCUENTRO Y PERDÓN
        ====================================================== --}}
        <article class="organization-summary organization-summary--jep">

            <div class="organization-summary__identity">
                <img
                    src="{{ asset('assets/img/organizations/jep.svg') }}"
                    alt="Justicia, Encuentro y Perdón"
                    class="organization-summary__logo organization-summary__logo--jep"
                >

                <h3>
                    Justicia, Encuentro<br>
                    y Perdón
                </h3>

                <span class="organization-summary__badge organization-summary__badge--jep">
                    {{ __('dashboard.jep_title') }}
                </span>
            </div>

            {{-- Cifras --}}
            <div class="organization-summary__main organization-summary__main--jep">
                <p class="organization-summary__eyebrow organization-summary__eyebrow--jep">
                    {{ __('dashboard.key_figures') }}
                </p>

                <div class="jep-summary-stats">

                    <div class="jep-summary-stat">
                        <div class="jep-summary-stat__icon">
                            <i class="bi bi-people"></i>
                        </div>

                        <span>{{ __('dashboard.stats.political_prisoners') }}</span>
                        <strong>1.875</strong>

                        <small class="metric-change metric-change--danger">
                            <i class="bi bi-arrow-up-short"></i>
                            5,2%
                        </small>

                        <small class="metric-period">
                            {{ __('dashboard.previous_month_comparison') }}
                        </small>
                    </div>

                    <div class="jep-summary-stat">
                        <div class="jep-summary-stat__icon">
                            <i class="bi bi-person-standing-dress"></i>
                        </div>

                        <span>{{ __('dashboard.stats.women') }}</span>
                        <strong>234</strong>

                        <small class="metric-change metric-change--danger">
                            <i class="bi bi-arrow-up-short"></i>
                            6,4%
                        </small>
                    </div>

                    <div class="jep-summary-stat">
                        <div class="jep-summary-stat__icon">
                            <i class="bi bi-heart-pulse"></i>
                        </div>

                        <span>{{ __('dashboard.stats.seriously_ill') }}</span>
                        <strong>142</strong>

                        <small class="metric-change metric-change--danger">
                            <i class="bi bi-arrow-up-short"></i>
                            18,3%
                        </small>
                    </div>

                    <div class="jep-summary-stat">
                        <div class="jep-summary-stat__icon">
                            <i class="bi bi-globe-americas"></i>
                        </div>

                        <span>
                            {{ __('dashboard.stats.foreign_dual_nationals') }}
                        </span>

                        <strong>23</strong>

                        <small class="metric-change metric-change--danger">
                            <i class="bi bi-arrow-up-short"></i>
                            21,1%
                        </small>
                    </div>

                    <div class="jep-summary-stat">
                        <div class="jep-summary-stat__icon">
                            <i class="bi bi-unlock"></i>
                        </div>

                        <span>{{ __('dashboard.stats.releases') }}</span>
                        <strong>87</strong>

                        <small class="metric-change metric-change--success">
                            <i class="bi bi-arrow-up-short"></i>
                            12,9%
                        </small>
                    </div>

                </div>
            </div>

            {{-- Alerta --}}
            <aside class="jep-monthly-alert">
                <div class="jep-monthly-alert__title">
                    <span>
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>

                    <strong>{{ __('dashboard.monthly_alert') }}</strong>
                </div>

                <p>
                    Aumento de traslados irregulares de detenidos a cárceles
                    de alta seguridad sin notificación a familiares ni abogados.
                </p>
            </aside>

            {{-- Pie completo de JEP --}}
            <div class="organization-summary__meta organization-summary__meta--jep">
                <span></span>

                <a href="{{ route('organizations.jep') }}">
                    {{ __('dashboard.view_full_dashboard') }}
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        </article>

        {{-- =====================================================
             ACCESO A LA JUSTICIA
        ====================================================== --}}
        <article class="organization-summary organization-summary--acceso">

            <div class="organization-summary__identity">
                <img
                    src="{{ asset('assets/img/organizations/acceso-justicia.png') }}"
                    alt="Acceso a la Justicia"
                    class="organization-summary__logo organization-summary__logo--horizontal"
                >

                <h3>
                    Acceso a la<br>
                    Justicia
                </h3>

                <span class="organization-summary__badge organization-summary__badge--acceso">
                    {{ __('dashboard.accesojusticia_title') }}
                </span>
            </div>

            <div class="organization-summary__main">
                <p class="organization-summary__eyebrow organization-summary__eyebrow--acceso">
                    {{ __('dashboard.latest_content') }}
                </p>

                <div class="organization-posts-grid">

                    @forelse (($accesoPosts ?? collect())->take(3) as $post)
                        <a
                            href="{{ $post['url'] ?? '#' }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="organization-post-mini"
                        >
                            <img
                                src="{{ $post['image'] ?? asset('assets/img/placeholders/article.svg') }}"
                                alt="{{ $post['title'] ?? '' }}"
                            >

                            <div>
                                <h4>{{ $post['title'] ?? '' }}</h4>

                                <time>
                                    {{ $post['date'] ?? '' }}
                                </time>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">
                            No se encontraron publicaciones recientes con #AlertaLegal.
                        </p>
                    @endforelse

                </div>

                <div class="organization-summary__meta">
                    <span></span>

                    <a href="{{ route('organizations.acceso-justicia') }}">
                        {{ __('dashboard.view_more_publications') }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </article>

        {{-- =====================================================
             OBSERVATORIO VENEZOLANO DE FAKE NEWS
        ====================================================== --}}
        <article class="organization-summary organization-summary--fake-news">

            <div class="organization-summary__identity">
                <img
                    src="{{ asset('assets/img/organizations/fake-news-a.webp') }}"
                    alt="Observatorio Venezolano de Fake News"
                    class="organization-summary__logo organization-summary__logo--fake-news"
                >

                <h3>
                    Observatorio Venezolano<br>
                    de Fake News
                </h3>

                <span class="organization-summary__badge-fake organization-summary__badge--fake-news">
                    {{ __('dashboard.fakenews_title') }}
                </span>
            </div>

            <div class="organization-summary__main">
                <div class="fake-news-overview">

                    <div class="fake-news-left">

                        @php
                            /*
                             * Datos temporales hasta implementar
                             * la carga desde el panel administrativo.
                             */
                            $ovfnSocialNetworks = [
                                [
                                    'key' => 'tiktok',
                                    'name' => 'Tik Tok',
                                    'total' => 48,
                                ],
                                [
                                    'key' => 'whatsapp',
                                    'name' => 'WhatsApp',
                                    'total' => 41,
                                ],
                                [
                                    'key' => 'x',
                                    'name' => 'X',
                                    'total' => 17,
                                ],
                                [
                                    'key' => 'instagram',
                                    'name' => 'Instagram',
                                    'total' => 16,
                                ],
                                [
                                    'key' => 'facebook',
                                    'name' => 'Facebook',
                                    'total' => 11,
                                ],
                                
                                
                                
                            ];

                            $ovfnSocialTotal = collect($ovfnSocialNetworks)->sum('total');

                            $ovfnSocialNetworks = collect($ovfnSocialNetworks)
                                ->map(function ($network) use ($ovfnSocialTotal) {
                                    $network['percentage'] = $ovfnSocialTotal > 0
                                        ? round(($network['total'] / $ovfnSocialTotal) * 100, 1)
                                        : 0;

                                    return $network;
                                });
                        @endphp

                        <h4 class="overview-section-title">
                            {{ __('dashboard.where_it_spreads') }}
                        </h4>

                        <div class="panorama-ovfn__networks">
                            @foreach ($ovfnSocialNetworks as $network)
                                <div class="panorama-ovfn__network">
                                    <span class="panorama-ovfn__network-icon" aria-hidden="true">
                                        <i class="fa-brands fa-{{ $network['key'] === 'x' ? 'x-twitter' : $network['key'] }}"></i>
                                    </span>

                                    <span class="panorama-ovfn__network-name">
                                        {{ $network['name'] }}
                                    </span>

                                    <strong class="panorama-ovfn__network-total">
                                        {{ number_format($network['total'], 0, ',', '.') }}
                                    </strong>

                                    <span class="panorama-ovfn__network-percentage">
                                        {{ number_format($network['percentage'], 1) }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <div class="fake-news-right">

                        <h4 class="overview-section-title">
                            {{ __('dashboard.featured_verification') }}
                        </h4>

                        <a href="#" class="fake-news-featured">

                            <img src="{{ asset('assets/img/organizations/fake-news-x.png') }}" alt="">

                            <div class="fake-news-featured__content">

                                <small>{{ __('dashboard.noti_fake') }}</small>

                                <p style="font-size: 12px;">
                                    Bukele y los terremotos en Venezuela:
                                    la imagen falsa que circula en redes
                                </p>

                                <time>27 jul. 2026</time>

                            </div>

                        </a>

                    </div>
                </div>

                <div class="organization-summary__meta">
                    <span>
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        <span>{{ __('dashboard.disinformation_since') }}</span>
                    </span>

                    <a href="{{ route('organizations.fake-news') }}">
                        {{ __('dashboard.view_full_dashboard') }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </article>

        {{-- =====================================================
             OBSERVATORIO DE UNIVERSIDADES
        ====================================================== --}}
        <article class="organization-summary organization-summary--obu">

            <div class="organization-summary__identity">
                <img
                    src="{{ asset('assets/img/organizations/obu.png') }}"
                    alt="Observatorio de Universidades"
                    class="organization-summary__logo organization-summary__logo--obu"
                >

                <h3>
                    Observatorio de<br>
                    Universidades
                </h3>

                <span class="organization-summary__badge organization-summary__badge--obu">
                    {{ __('dashboard.university_title') }}
                </span>
            </div>

            <div class="organization-summary__main">
                <p class="organization-summary__eyebrow organization-summary__eyebrow--obu">
                    {{ __('dashboard.latest_content') }}
                </p>

                <div class="obu-panorama-metrics">
                    <div class="obu-panorama-metric obu-panorama-metric--reports">
                        <span class="obu-panorama-metric__icon"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
                        <div class="obu-panorama-metric__content">
                            <span class="obu-panorama-metric__label">{{ __('dashboard.obu.total_reports') }}</span>
                            <strong class="obu-panorama-metric__value">222</strong>
                            <small class="obu-panorama-metric__subtitle">{{ __('dashboard.obu.last_six_months') }}</small>
                        </div>
                    </div>

                    <div class="obu-panorama-metric obu-panorama-metric--categories">
                        <span class="obu-panorama-metric__icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                        <div class="obu-panorama-metric__content">
                            <span class="obu-panorama-metric__label">{{ __('dashboard.obu.documented_categories') }}</span>
                            <strong class="obu-panorama-metric__value">8</strong>
                            <small class="obu-panorama-metric__subtitle">{{ __('dashboard.obu.three_main_areas') }}</small>
                        </div>
                    </div>

                    <div class="obu-panorama-metric obu-panorama-metric--protests">
                        <span class="obu-panorama-metric__icon"><i class="bi bi-megaphone-fill" aria-hidden="true"></i></span>
                        <div class="obu-panorama-metric__content">
                            <span class="obu-panorama-metric__label">{{ __('dashboard.obu.registered_protests') }}</span>
                            <strong class="obu-panorama-metric__value">75</strong>
                            <small class="obu-panorama-metric__subtitle">{{ __('dashboard.obu.five_modalities') }}</small>
                        </div>
                    </div>

                    <div class="obu-panorama-metric obu-panorama-metric--period">
                        <span class="obu-panorama-metric__icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
                        <div class="obu-panorama-metric__content">
                            <span class="obu-panorama-metric__label">{{ __('dashboard.obu.analysis_period') }}</span>
                            <strong class="obu-panorama-metric__value obu-panorama-metric__value--period">{{ __('dashboard.obu.january_june_2026') }}</strong>
                            <small class="obu-panorama-metric__subtitle">{{ __('dashboard.obu.last_six_months_short') }}</small>
                        </div>
                    </div>
                </div>

                <div class="organization-summary__meta">
                    <span></span>

                    <a href="{{ route('organizations.universidades') }}">
                        {{ __('dashboard.view_full_dashboard') }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </article>

    </div>
</section>
