<x-layouts::admin :title="'Analítica | OVFN'">
    <style>
        .ovfn-platform-editor { padding: 24px; }
        @media (max-width: 767.98px) {
            .ovfn-platform-editor { padding: 16px; }
        }
    </style>
    @php
        $summary = $summary ?? [];
        $origin = $panelOrigin ?? ['pulso' => 0, 'direct' => 0, 'total' => 0];
        $content = $contentClicks ?? ['x_post' => 0, 'noti_fake' => 0, 'analysis' => 0, 'total' => 0];
        $contentTotal = (int) ($content['total'] ?? 0);
        $percent = fn ($value) => $contentTotal ? (int) round(((int) $value / $contentTotal) * 100) : 0;
        $start = today()->subDays(29)->locale('es');
        $period = $start->isoFormat('D MMM YYYY').' – '.today()->locale('es')->isoFormat('D MMM YYYY');
    @endphp
    <main class="access-justice-dashboard ovfn-dashboard">
        <header class="access-justice-header">
            <div class="access-justice-header__copy"><span class="access-justice-header__accent"></span><div><h1>Observatorio Venezolano de Fake News</h1><p>Resumen de analítica y rendimiento del módulo</p></div></div>
            <div class="access-justice-header__actions"><span class="access-justice-period"><i class="bi bi-calendar3"></i><span>{{ $period }}</span></span><button class="access-justice-export" disabled><i class="bi bi-download"></i> Exportar</button></div>
        </header>
        <section class="access-justice-kpis">
            @foreach ([['orange','fa-arrow-pointer','Clics desde Pulso',$summary['home_navigation_clicks'] ?? 0,'Navegación hacia el panel'],['blue','fa-eye','Visitas al portal Pulso Venezuela',$summary['portal_views'] ?? 0,'Total de visitas al portal'],['green','fa-window-maximize','Visitas al panel Fake News',$summary['organization_views'] ?? 0,'Entradas al panel del módulo'],['purple','fa-bullhorn','Clics en contenidos',$contentTotal,'X: '.($content['x_post'] ?? 0).' · Noti Fake: '.($content['noti_fake'] ?? 0).' · En profundidad: '.($content['analysis'] ?? 0)]] as $kpi)
                <article class="analytics-kpi-card analytics-kpi-card--{{ $kpi[0] }}"><div class="analytics-kpi-main"><span class="analytics-kpi-icon"><i class="fa-solid {{ $kpi[1] }}"></i></span><div class="analytics-kpi-content"><p class="analytics-kpi-title">{{ $kpi[2] }}</p><strong class="analytics-kpi-value">{{ number_format($kpi[3]) }}</strong><small class="analytics-kpi-description">{{ $kpi[4] }}</small></div></div><svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none"><path d="M0 28 C30 18 50 34 78 25 S125 14 154 25 S205 34 232 21 S280 15 320 20" /></svg></article>
            @endforeach
        </section>
        <section class="access-justice-charts ovfn-analytics-charts"><article class="access-justice-card access-justice-chart-card"><header class="access-justice-card__header"><div><h2>Visitas por fecha</h2><p>Comparación de visitas al portal y al panel</p></div><span class="access-justice-chart-period">Diaria</span></header><div class="access-justice-line-chart"><canvas id="ovfnVisitsChart"></canvas></div></article><div class="access-justice-charts-side"><article class="access-justice-card access-justice-origin-card"><header class="access-justice-card__header"><div><h2>Origen de visitas al panel</h2><p>Distribución de accesos registrados</p></div></header><div class="access-justice-origin-card__content"><div class="access-justice-donut"><canvas id="ovfnOriginChart"></canvas></div><div class="access-justice-origin-legend"><div><span>Desde Pulso</span><strong>{{ $origin['pulso'] }}</strong></div><div><span>Acceso directo</span><strong>{{ $origin['direct'] }}</strong></div><div class="access-justice-origin-legend__total"><span>Total</span><strong>{{ $origin['total'] }}</strong></div></div></div></article><article class="access-justice-card access-justice-interaction-card"><header class="access-justice-card__header"><div><h2>Interacción con contenidos</h2><p>Distribución de clics registrados</p></div></header><div class="access-justice-interaction-total"><strong>{{ $contentTotal }}</strong><span>Clics totales</span></div><div class="access-justice-interaction-breakdown">@foreach ([['x_post','Tweets','orange'],['noti_fake','Noti Fake','purple'],['analysis','En profundidad','blue']] as [$key,$label,$color])<div class="access-justice-interaction-row"><div class="access-justice-interaction-row__label"><span>{{ $label }}</span><strong>{{ $content[$key] ?? 0 }} <small>{{ $percent($content[$key] ?? 0) }}%</small></strong></div><div class="access-justice-interaction-bar"><span style="width: {{ $percent($content[$key] ?? 0) }}%"></span></div></div>@endforeach</div></article></div></section>
        <article class="access-justice-card ovfn-content-chart-card"><header class="access-justice-card__header"><div><h2>Clics en contenidos por día</h2><p>Evolución de interacciones registradas</p></div><span class="access-justice-chart-period">Diaria</span></header><div class="access-justice-line-chart"><canvas id="ovfnContentClicksChart"></canvas></div></article>
        @can('edit ovfn metrics')
            <section id="ovfn-verification-edit" class="ovfn-verification-editor access-justice-card"><h2>Editar Total de Verificaciones</h2><form method="POST" action="{{ route('admin.ovfn.total-verifications.update') }}">@csrf @method('PATCH')<div class="row g-3"><div class="col-md-6"><label for="ovfn-total">Total de Verificaciones</label><input id="ovfn-total" class="form-control" type="number" min="0" name="total" required value="{{ old('total', $currentVerificationTotal?->total) }}"></div><div class="col-md-6"><label for="ovfn-data-date">Actualizado hasta</label><input id="ovfn-data-date" class="form-control" type="date" name="data_date" required value="{{ old('data_date', $currentVerificationTotal?->data_date?->format('Y-m-d')) }}"></div></div><button class="btn btn-primary mt-3" type="submit">Guardar cambios</button></form></section>
        @endcan
        @php
            $platformItems = collect($currentDistribution?->items ?? [])->keyBy('platform');
            $platformLabels = ['tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'x' => 'X', 'instagram' => 'Instagram', 'facebook' => 'Facebook'];
            $platformTotal = max(0, (int) $platformItems->sum('value'));
        @endphp
        <section class="ovfn-platform-editor access-justice-card">
            <header class="access-justice-card__header"><div><h2>Editar Dónde circula la desinformación</h2><p>Actualiza la distribución por plataforma mostrada en el panel público.</p></div></header>
            @can('edit ovfn metrics')
                <form method="POST" action="{{ route('admin.ovfn.platform-distribution.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12"><label for="ovfn-platform-date">Fecha desde</label><input id="ovfn-platform-date" class="form-control" type="date" name="data_from_date" required value="{{ old('data_from_date', $currentDistribution?->data_from_date?->format('Y-m-d')) }}"></div>
                        @foreach ($platformLabels as $key => $label)
                            <div class="col-md-6"><label for="ovfn-platform-{{ $key }}">{{ $label }}</label><div class="d-flex align-items-center gap-2"><input id="ovfn-platform-{{ $key }}" class="form-control" type="number" min="0" name="platforms[{{ $key }}]" required value="{{ old('platforms.'.$key, $platformItems->get($key)?->value ?? 0) }}"><span class="text-muted text-sm" data-platform-percent="{{ $key }}">{{ $platformTotal > 0 ? number_format((($platformItems->get($key)?->value ?? 0) / $platformTotal) * 100, 1) : '0.0' }}%</span></div></div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Guardar actualización</button>
                </form>
            @else
                <div class="row g-3">@foreach ($platformLabels as $key => $label)<div class="col-md-6"><span class="text-muted text-sm">{{ $label }}</span><strong class="d-block">{{ $platformItems->get($key)?->value ?? 0 }} <small class="text-muted">{{ $platformTotal > 0 ? number_format((($platformItems->get($key)?->value ?? 0) / $platformTotal) * 100, 1) : '0.0' }}%</small></strong></div>@endforeach</div>
            @endcan
        </section>
        <section class="ovfn-history access-justice-card access-justice-ranking"><header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de Total de Verificaciones</h2><p>Versiones editoriales, solo lectura</p></div></header><div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Valor</th><th>Actualizado hasta</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr></thead><tbody>@forelse ($verificationHistory as $version)<tr><td><span class="ovfn-history-value-badge">{{ $version->total }}</span></td><td>{{ $version->data_date->format('d/m/Y') }}</td><td>{{ $version->valid_from->format('d/m/Y H:i') }}</td><td>@if ($version->valid_until)<span>{{ $version->valid_until->format('d/m/Y H:i') }}</span>@else<span class="ovfn-history-current-badge">Vigente</span>@endif</td><td>{{ $version->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="5">No hay historial registrado.</td></tr>@endforelse</tbody></table></div></section>
        <section class="ovfn-history access-justice-card access-justice-ranking"><header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de distribución por plataforma</h2><p>Versiones editoriales, solo lectura</p></div></header><div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Fecha de datos</th><th>TikTok</th><th>WhatsApp</th><th>X</th><th>Instagram</th><th>Facebook</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr></thead><tbody>@forelse ($distributionHistory as $version)<tr><td>{{ $version->data_from_date->format('d/m/Y') }}</td>@php($historyItems = $version->items->keyBy('platform'))@foreach (array_keys($platformLabels) as $platform)<td>{{ $historyItems->get($platform)?->value ?? 0 }}</td>@endforeach<td>{{ $version->valid_from->format('d/m/Y H:i') }}</td><td>@if ($version->valid_until){{ $version->valid_until->format('d/m/Y H:i') }}@else<span class="ovfn-history-current-badge">Vigente</span>@endif</td><td>{{ $version->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="9">No hay historial registrado.</td></tr>@endforelse</tbody></table></div></section>
    </main>
    @include('components.flash-toast', ['toasts' => [
        session('ovfn_verification_success') ? ['type' => 'success', 'message' => session('ovfn_verification_success')] : (session('ovfn_verification_info') ? ['type' => 'info', 'message' => session('ovfn_verification_info')] : null),
        session('ovfn_distribution_success') ? ['type' => 'success', 'message' => session('ovfn_distribution_success')] : (session('ovfn_distribution_info') ? ['type' => 'info', 'message' => session('ovfn_distribution_info')] : null),
    ]])
    <script>
        (() => {
            const dashboard = document.querySelector('.ovfn-dashboard');
            const form = dashboard?.querySelector('.ovfn-platform-editor form');
            const inputs = form ? [...form.querySelectorAll('input[name^="platforms["]')] : [];

            const updatePlatformPercentages = () => {
                const values = inputs.map((input) => Math.max(0, Number(input.value) || 0));
                const total = values.reduce((sum, value) => sum + value, 0);

                inputs.forEach((input, index) => {
                    const percentage = total > 0 ? (values[index] / total) * 100 : 0;
                    const output = form.querySelector(`[data-platform-percent="${input.name.match(/\[(.*?)\]/)?.[1]}"]`);
                    if (output) output.textContent = `${percentage.toFixed(1)}%`;
                });
            };

            inputs.forEach((input) => input.addEventListener('input', updatePlatformPercentages));
            updatePlatformPercentages();

            if (dashboard) {
                const histories = [...dashboard.querySelectorAll('.ovfn-history')];
                const totalHistory = histories.find((card) => card.textContent.includes('Historial de Total de Verificaciones'));
                const platformEditor = dashboard.querySelector('.ovfn-platform-editor');
                if (totalHistory && platformEditor) dashboard.insertBefore(totalHistory, platformEditor);
            }
        })();
    </script>
    <script type="application/json" id="ovfnAnalyticsData">@json(['visits' => $visitsChart ?? [], 'content' => $contentClicksChart ?? [], 'origin' => $origin])</script>
    @vite('resources/js/admin-analytics.js')
</x-layouts::admin>
