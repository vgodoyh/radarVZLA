<section class="access-justice-card access-justice-ranking">
    <header class="access-justice-card__header access-justice-ranking__header">
        <div>
            <h2>Alertas más consultadas</h2>
            <p>Ranking según interacciones registradas</p>
        </div>
    </header>

    <p class="alert-ranking-scroll-hint">
        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
        Desliza horizontalmente para ver todas las columnas
    </p>

    <div
        class="access-justice-table-wrap alert-ranking-table-scroll"
        wire:loading.class="is-loading"
        wire:target="gotoPage,previousPage,nextPage"
    >
        <table>
            <thead>
                <tr>
                    <th class="access-justice-rank-column">#</th>
                    <th class="access-justice-alert-column">Alerta</th>
                    <th class="access-justice-number-column">Clics desde Pulso</th>
                    <th class="access-justice-number-column">Clics desde Panel</th>
                    <th class="access-justice-number-column access-justice-total-column">Total clics</th>
                    <th class="access-justice-trend-column">Tendencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ranking as $item)
                    <tr wire:key="alert-ranking-row-{{ $item['publication_id'] }}">
                        <td class="access-justice-rank-column"><span>{{ $ranking->firstItem() + $loop->index }}</span></td>
                        <td class="access-justice-alert-column">
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="access-justice-alert-link" title="{{ $item['alert'] }}">
                                {{ $item['alert'] }}
                            </a>
                        </td>
                        <td class="access-justice-number-column">{{ $item['home_clicks'] }}</td>
                        <td class="access-justice-number-column">{{ $item['organization_clicks'] }}</td>
                        <td class="access-justice-number-column access-justice-total-column access-justice-total-clicks">{{ $item['total_clicks'] }}</td>
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

        <span class="access-justice-ranking-loading" wire:loading wire:target="gotoPage,previousPage,nextPage" aria-live="polite">
            Actualizando…
        </span>
    </div>

    @if ($ranking->hasPages())
        @php
            $rankingPageName = 'alertsPage';
            $rankingPage = $ranking->currentPage();
            $rankingLastPage = $ranking->lastPage();
            $rankingVisiblePages = $ranking->getUrlRange(
                max(1, $rankingPage - 1),
                min($rankingLastPage, $rankingPage + 1),
            );
        @endphp
        <nav class="access-justice-pagination" aria-label="Paginación de alertas más consultadas">
            @if ($ranking->onFirstPage())
                <button type="button" class="access-justice-pagination__control is-disabled" disabled aria-label="Página anterior">‹</button>
            @else
                <button type="button" class="access-justice-pagination__control" wire:click="previousPage('{{ $rankingPageName }}')" wire:loading.attr="disabled" wire:target="gotoPage,previousPage,nextPage" aria-label="Página anterior">‹</button>
            @endif

            @if ($rankingPage > 2)
                <button type="button" class="access-justice-pagination__page" wire:click="gotoPage(1, '{{ $rankingPageName }}')" wire:loading.attr="disabled" wire:target="gotoPage,previousPage,nextPage">1</button>
                @if ($rankingPage > 3)
                    <span class="access-justice-pagination__ellipsis" aria-hidden="true">…</span>
                @endif
            @endif

            @foreach ($rankingVisiblePages as $page => $url)
                <button
                    type="button"
                    class="access-justice-pagination__page{{ $page === $rankingPage ? ' is-active' : '' }}"
                    wire:click="gotoPage({{ $page }}, '{{ $rankingPageName }}')"
                    wire:loading.attr="disabled"
                    wire:target="gotoPage,previousPage,nextPage"
                    @if ($page === $rankingPage) aria-current="page" @endif
                >{{ $page }}</button>
            @endforeach

            @if ($rankingPage < $rankingLastPage - 1)
                @if ($rankingPage < $rankingLastPage - 2)
                    <span class="access-justice-pagination__ellipsis" aria-hidden="true">…</span>
                @endif
                <button type="button" class="access-justice-pagination__page" wire:click="gotoPage({{ $rankingLastPage }}, '{{ $rankingPageName }}')" wire:loading.attr="disabled" wire:target="gotoPage,previousPage,nextPage">{{ $rankingLastPage }}</button>
            @endif

            @if ($ranking->hasMorePages())
                <button type="button" class="access-justice-pagination__control" wire:click="nextPage('{{ $rankingPageName }}')" wire:loading.attr="disabled" wire:target="gotoPage,previousPage,nextPage" aria-label="Página siguiente">›</button>
            @else
                <button type="button" class="access-justice-pagination__control is-disabled" disabled aria-label="Página siguiente">›</button>
            @endif
        </nav>
    @endif
</section>
