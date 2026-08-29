<x-layouts::admin :title="'Analítica | Acceso a la Justicia'">
    @php
        $lastSyncAt = $sync['last_synced_at']?->copy()
            ->setTimezone('America/Caracas')
            ->locale('es');
        $panelOriginPulso = (int) $panelOrigin['pulso'];
        $panelOriginDirect = (int) $panelOrigin['direct'];
        $panelOriginTotal = (int) $panelOrigin['total'];
        $alertClicksTotal = (int) $summary['alert_clicks'];
        $alertClicksPulso = (int) $summary['home_clicks'];
        $alertClicksPanel = (int) $summary['organization_clicks'];
        $alertClicksPulsoPercentage = $alertClicksTotal > 0
            ? (int) round(($alertClicksPulso / $alertClicksTotal) * 100)
            : 0;
        $alertClicksPanelPercentage = $alertClicksTotal > 0
            ? (int) round(($alertClicksPanel / $alertClicksTotal) * 100)
            : 0;
        $analyticsPeriodStart = today()->subDays(29)->locale('es');
        $analyticsPeriodEnd = today()->locale('es');
        $analyticsPeriodLabel = sprintf(
            '%s – %s',
            $analyticsPeriodStart->isoFormat('D MMM YYYY'),
            $analyticsPeriodEnd->isoFormat('D MMM YYYY')
        );
        $analyticsFrontendData = [
            'chart' => $chart,
            'origin' => [
                'pulso' => $panelOriginPulso,
                'direct' => $panelOriginDirect,
                'total' => $panelOriginTotal,
            ],
        ];
    @endphp

    <main class="access-justice-dashboard">
        <header class="access-justice-header">
            <div class="access-justice-header__copy">
                <span class="access-justice-header__accent" aria-hidden="true"></span>
                <div>
                    <h1>Acceso a la Justicia</h1>
                    <p>Resumen de analítica y rendimiento del módulo</p>
                </div>
            </div>

            <div class="access-justice-header__actions" aria-label="Controles del período">
                <span class="access-justice-period" aria-label="Período actual del dashboard">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span>{{ $analyticsPeriodLabel }}</span>
                </span>
                <button
                    type="button"
                    class="access-justice-export"
                    disabled
                    aria-disabled="true"
                    title="La exportación aún no está disponible"
                >
                    <i class="bi bi-download" aria-hidden="true"></i>
                    <span>Exportar</span>
                </button>
            </div>
        </header>

        <section class="access-justice-kpis" aria-label="Indicadores principales">
            <article class="analytics-kpi-card analytics-kpi-card--orange">
                <div class="analytics-kpi-main">
                    <span class="analytics-kpi-icon" aria-hidden="true"><i class="fa-solid fa-arrow-pointer"></i></span>
                    <div class="analytics-kpi-content">
                        <p class="analytics-kpi-title">Clics desde Pulso</p>
                        <strong class="analytics-kpi-value">{{ number_format($summary['home_navigation_clicks'], 0, ',', '.') }}</strong>
                        <small class="analytics-kpi-description">Navegación hacia el panel</small>
                    </div>
                </div>
                <svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 30 C28 27 39 34 61 27 S96 12 119 24 S151 35 175 22 S210 12 232 24 S273 34 294 21 S311 16 320 18" /></svg>
            </article>

            <article class="analytics-kpi-card analytics-kpi-card--blue">
                <div class="analytics-kpi-main">
                    <span class="analytics-kpi-icon" aria-hidden="true"><i class="fa-regular fa-eye"></i></span>
                    <div class="analytics-kpi-content">
                        <p class="analytics-kpi-title">Visitas al portal Pulso Venezuela</p>
                        <strong class="analytics-kpi-value">{{ number_format($summary['portal_views'], 0, ',', '.') }}</strong>
                        <small class="analytics-kpi-description">Total de visitas al portal</small>
                    </div>
                </div>
                <svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 24 C24 21 39 17 58 23 S91 36 112 25 S146 11 169 21 S203 31 226 23 S260 9 281 20 S308 28 320 14" /></svg>
            </article>

            <article class="analytics-kpi-card analytics-kpi-card--green">
                <div class="analytics-kpi-main">
                    <span class="analytics-kpi-icon" aria-hidden="true"><i class="fa-regular fa-window-maximize"></i></span>
                    <div class="analytics-kpi-content">
                        <p class="analytics-kpi-title">Visitas al panel Acceso a la Justicia</p>
                        <strong class="analytics-kpi-value">{{ number_format($summary['organization_views'], 0, ',', '.') }}</strong>
                        <small class="analytics-kpi-description">Entradas al panel del módulo</small>
                    </div>
                </div>
                <svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 31 C21 29 42 22 63 28 S98 33 118 20 S151 7 174 20 S207 32 229 25 S263 17 282 23 S307 27 320 12" /></svg>
            </article>

            <article class="analytics-kpi-card analytics-kpi-card--purple">
                <div class="analytics-kpi-main">
                    <span class="analytics-kpi-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
                    <div class="analytics-kpi-content">
                        <p class="analytics-kpi-title">Clics en alertas</p>
                        <strong class="analytics-kpi-value">{{ number_format($summary['alert_clicks'], 0, ',', '.') }}</strong>
                        <small class="analytics-kpi-description">
                            Pulso: {{ number_format($summary['home_clicks'], 0, ',', '.') }} ·
                            Panel: {{ number_format($summary['organization_clicks'], 0, ',', '.') }}
                        </small>
                    </div>
                </div>
                <svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 27 C25 30 40 18 62 25 S96 36 118 27 S150 14 173 24 S207 31 230 19 S267 11 286 22 S307 29 320 17" /></svg>
            </article>
        </section>

        @if (session('sync_success'))
            <div class="alert alert-success text-white access-justice-feedback" role="status">{{ session('sync_success') }}</div>
        @endif
        @if (session('sync_error'))
            <div class="alert alert-danger text-white access-justice-feedback" role="alert">{{ session('sync_error') }}</div>
        @endif

        <section class="access-justice-card access-justice-sync" aria-label="Estado de sincronización">
            <div class="access-justice-sync__item">
                <span class="access-justice-sync__icon" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                <div>
                    <p>Alertas sincronizadas</p>
                    <strong id="accessJusticeAlertsCount">{{ number_format($sync['alerts_count'], 0, ',', '.') }}</strong>
                    <small>Publicaciones disponibles</small>
                </div>
            </div>

            <div class="access-justice-sync__item">
                <span class="access-justice-sync__icon" aria-hidden="true"><i class="bi bi-calendar3"></i></span>
                <div>
                    <p>Última sincronización</p>
                    @if ($lastSyncAt)
                        <strong class="access-justice-sync__date" id="accessJusticeLastSyncedAt">{{ $lastSyncAt->isoFormat('D MMM YYYY · h:mm a') }}</strong>
                        <small id="accessJusticeLastSyncedRelative">{{ ucfirst($lastSyncAt->diffForHumans(['parts' => 2, 'join' => true])) }}</small>
                    @else
                        <strong class="access-justice-sync__empty">Sin sincronizaciones registradas</strong>
                    @endif
                </div>
            </div>

            <div class="access-justice-sync__status-action" id="accessJusticeSyncForm" data-status-url="{{ route('admin.acceso-justicia.sync.status') }}" data-sync-status="{{ $sync['status'] ?? '' }}">
            <div class="access-justice-sync__item">
                <span class="access-justice-sync__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    @if ($sync['status'] === 'success')
                        <span id="accessJusticeSyncStatus" class="access-justice-status access-justice-status--success"><i class="fa-solid fa-circle"></i> Sincronización exitosa</span>
                    @elseif ($sync['status'] === 'failed')
                        <span id="accessJusticeSyncStatus" class="access-justice-status access-justice-status--failed"><i class="fa-solid fa-circle"></i> Error en sincronización</span>
                    @elseif ($sync['status'] === 'running')
                        <span id="accessJusticeSyncStatus" class="access-justice-status access-justice-status--running"><i class="fa-solid fa-circle"></i> Sincronizando...</span>
                    @else
                        <strong class="access-justice-sync__empty">No disponible</strong>
                        <small>Aún no hay ejecuciones registradas</small>
                    @endif
                </div>
            </div>

            </div>
        </section>

        <section class="access-justice-charts" aria-label="Gráficos de visitas">
            <article class="access-justice-card access-justice-chart-card">
                <header class="access-justice-card__header">
                    <div>
                        <h2>Visitas por fecha</h2>
                        <p>Evolución de visitas registradas</p>
                    </div>
                    <span class="access-justice-chart-period" aria-hidden="true">Diaria</span>
                </header>
                <div class="access-justice-line-chart">
                    <canvas id="accessJusticeAnalyticsChart" aria-label="Visitas por fecha" role="img"></canvas>
                </div>
            </article>

            <div class="access-justice-charts-side">
            <article class="access-justice-card access-justice-origin-card">
                <header class="access-justice-card__header">
                    <div>
                        <h2>Origen de visitas al panel</h2>
                        <p>Distribución de accesos registrados</p>
                    </div>
                </header>
                <div class="access-justice-origin-card__content">
                    <div class="access-justice-donut">
                        <canvas id="accessJusticeOriginChart" aria-label="Origen de visitas al panel" role="img"></canvas>
                    </div>
                    <div class="access-justice-origin-legend">
                        <div><span><i class="access-justice-dot access-justice-dot--orange"></i>Desde Pulso</span><strong>{{ number_format($panelOriginPulso, 0, ',', '.') }}</strong></div>
                        <div><span><i class="access-justice-dot access-justice-dot--blue"></i>Acceso directo</span><strong>{{ number_format($panelOriginDirect, 0, ',', '.') }}</strong></div>
                        <div class="access-justice-origin-legend__total"><span>Total</span><strong>{{ number_format($panelOriginTotal, 0, ',', '.') }}</strong></div>
                    </div>
                </div>
            </article>
                <article class="access-justice-card access-justice-interaction-card">
                    <header class="access-justice-card__header">
                        <div>
                            <h2>Interacción con alertas</h2>
                            <p>Distribución de clics registrados</p>
                        </div>
                    </header>
                    <div class="access-justice-interaction-total">
                        <strong>{{ number_format($alertClicksTotal, 0, ',', '.') }}</strong>
                        <span>Clics totales</span>
                    </div>
                    <div class="access-justice-interaction-breakdown">
                        <div class="access-justice-interaction-row">
                            <div class="access-justice-interaction-row__label">
                                <span><i class="access-justice-dot access-justice-dot--orange"></i>Desde Pulso</span>
                                <strong>{{ number_format($alertClicksPulso, 0, ',', '.') }} <small>{{ $alertClicksPulsoPercentage }}%</small></strong>
                            </div>
                            <div class="access-justice-interaction-bar"><span style="width: {{ $alertClicksPulsoPercentage }}%"></span></div>
                        </div>
                        <div class="access-justice-interaction-row">
                            <div class="access-justice-interaction-row__label">
                                <span><i class="access-justice-dot access-justice-dot--purple"></i>Desde el panel</span>
                                <strong>{{ number_format($alertClicksPanel, 0, ',', '.') }} <small>{{ $alertClicksPanelPercentage }}%</small></strong>
                            </div>
                            <div class="access-justice-interaction-bar"><span style="width: {{ $alertClicksPanelPercentage }}%"></span></div>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- Legacy Blade ranking replaced by the Livewire component.
        <section class="access-justice-card access-justice-ranking">
            <header class="access-justice-card__header access-justice-ranking__header">
                <div>
                    <h2>Alertas más consultadas</h2>
                    <p>Ranking según interacciones registradas</p>
                </div>
            </header>
            <div class="access-justice-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="access-justice-rank-column">#</th>
                            <th>Alerta</th>
                            <th class="access-justice-number-column">Clics desde Pulso</th>
                            <th class="access-justice-number-column">Clics desde Panel</th>
                            <th class="access-justice-number-column">Total clics</th>
                            <th class="access-justice-trend-column">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranking as $item)
                            <tr>
                                <td class="access-justice-rank-column"><span>{{ $ranking->firstItem() + $loop->index }}</span></td>
                                <td>
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="access-justice-alert-link" title="{{ $item['alert'] }}">
                                        {{ $item['alert'] }}
                                    </a>
                                </td>
                                <td class="access-justice-number-column">{{ $item['home_clicks'] }}</td>
                                <td class="access-justice-number-column">{{ $item['organization_clicks'] }}</td>
                                <td class="access-justice-number-column access-justice-total-clicks">{{ $item['total_clicks'] }}</td>
                                <td class="access-justice-trend-column">
                                    <canvas class="access-justice-sparkline" data-series='@json($item['tendency'])' aria-label="Tendencia de clics de {{ $item['alert'] }}" role="img"></canvas>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="access-justice-empty-table">Todavía no hay clics registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ranking->hasPages())
                @php
                    $rankingPage = $ranking->currentPage();
                    $rankingLastPage = $ranking->lastPage();
                    $rankingVisiblePages = $ranking->getUrlRange(
                        max(1, $rankingPage - 1),
                        min($rankingLastPage, $rankingPage + 1),
                    );
                @endphp
                <nav class="access-justice-pagination" aria-label="Paginación de alertas más consultadas">
                    @if ($ranking->onFirstPage())
                        <span class="access-justice-pagination__control is-disabled" aria-hidden="true">‹</span>
                    @else
                        <a class="access-justice-pagination__control" href="{{ $ranking->previousPageUrl() }}" rel="prev" aria-label="Página anterior">‹</a>
                    @endif

                    @if ($rankingPage > 2)
                        <a class="access-justice-pagination__page" href="{{ $ranking->url(1) }}">1</a>
                        @if ($rankingPage > 3)
                            <span class="access-justice-pagination__ellipsis" aria-hidden="true">…</span>
                        @endif
                    @endif

                    @foreach ($rankingVisiblePages as $page => $url)
                        <a
                            class="access-justice-pagination__page{{ $page === $rankingPage ? ' is-active' : '' }}"
                            href="{{ $url }}"
                            @if ($page === $rankingPage) aria-current="page" @endif
                        >{{ $page }}</a>
                    @endforeach

                    @if ($rankingPage < $rankingLastPage - 1)
                        @if ($rankingPage < $rankingLastPage - 2)
                            <span class="access-justice-pagination__ellipsis" aria-hidden="true">…</span>
                        @endif
                        <a class="access-justice-pagination__page" href="{{ $ranking->url($rankingLastPage) }}">{{ $rankingLastPage }}</a>
                    @endif

                    @if ($ranking->hasMorePages())
                        <a class="access-justice-pagination__control" href="{{ $ranking->nextPageUrl() }}" rel="next" aria-label="Página siguiente">›</a>
                    @else
                        <span class="access-justice-pagination__control is-disabled" aria-hidden="true">›</span>
                    @endif
                </nav>
            @endif
        </section>
        --}}
        <livewire:admin.analytics.acceso-justicia-alert-ranking />
    </main>

    <script type="application/json" id="accessJusticeAnalyticsData">@json($analyticsFrontendData)</script>
    @vite('resources/js/admin-analytics.js')
</x-layouts::admin>
