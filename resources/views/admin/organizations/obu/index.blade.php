<x-layouts::admin :title="'Analítica | OBU'">
    @php
        $summary = $summary ?? [];
        $currentMetrics = $currentMetrics;
        $period = today()->subDays(29)->locale('es')->isoFormat('D MMM YYYY').' – '.today()->locale('es')->isoFormat('D MMM YYYY');
    @endphp

    <main class="access-justice-dashboard obu-dashboard">
        <header class="access-justice-header">
            <div class="access-justice-header__copy"><span class="access-justice-header__accent"></span><div><h1>Observatorio de Universidades</h1><p>Resumen de analítica y rendimiento del módulo</p></div></div>
            <div class="access-justice-header__actions"><span class="access-justice-period"><i class="bi bi-calendar3"></i><span>{{ $period }}</span></span><button class="access-justice-export" disabled><i class="bi bi-download"></i> Exportar</button></div>
        </header>

        <section class="access-justice-kpis">
            @foreach ([['orange','fa-arrow-pointer','Clics desde Pulso',$summary['home_navigation_clicks'] ?? 0,'Navegación hacia el panel'],['blue','fa-eye','Visitas al portal Pulso Venezuela',$summary['portal_views'] ?? 0,'Total de visitas al portal'],['green','fa-window-maximize','Visitas al panel OBU',$summary['organization_views'] ?? 0,'Entradas al módulo'],['purple','fa-bullhorn','Clics en contenidos',$summary['content_clicks'] ?? 0,'Interacciones registradas']] as $kpi)
                <article class="analytics-kpi-card analytics-kpi-card--{{ $kpi[0] }}"><div class="analytics-kpi-main"><span class="analytics-kpi-icon"><i class="fa-solid {{ $kpi[1] }}"></i></span><div class="analytics-kpi-content"><p class="analytics-kpi-title">{{ $kpi[2] }}</p><strong class="analytics-kpi-value">{{ number_format($kpi[3]) }}</strong><small class="analytics-kpi-description">{{ $kpi[4] }}</small></div></div><svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 28 C30 18 50 34 78 25 S125 14 154 25 S205 34 232 21 S280 15 320 20" /></svg></article>
            @endforeach
        </section>

        <section class="access-justice-charts obu-analytics-charts">
            <article class="access-justice-card access-justice-chart-card"><header class="access-justice-card__header"><div><h2>Visitas por fecha</h2><p>Visitas al portal y al panel OBU</p></div><span class="access-justice-chart-period">Diaria</span></header><div class="access-justice-line-chart"><canvas id="obuVisitsChart" aria-label="Visitas por fecha" role="img"></canvas></div></article>
            <div class="access-justice-charts-side"><article class="access-justice-card access-justice-origin-card"><header class="access-justice-card__header"><div><h2>Origen de visitas al panel</h2><p>Distribución de accesos registrados</p></div></header><div class="access-justice-origin-legend"><div><span>Desde Pulso</span><strong>{{ $panelOrigin['pulso'] ?? 0 }}</strong></div><div><span>Acceso directo</span><strong>{{ $panelOrigin['direct'] ?? 0 }}</strong></div><div class="access-justice-origin-legend__total"><span>Total</span><strong>{{ $panelOrigin['total'] ?? 0 }}</strong></div></div></article></div>
        </section>

        @can('edit obu metrics')
            <section class="access-justice-card obu-metrics-editor">
                <header class="access-justice-card__header"><div><h2>Editar cifras de OBU</h2><p>Actualiza las cifras mostradas en el panel público.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.metrics.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-3"><label for="obu-universities-monitored">Universidades monitoreadas</label><input id="obu-universities-monitored" class="form-control" type="number" min="0" name="universities_monitored" required value="{{ old('universities_monitored', $currentMetrics?->universities_monitored) }}"></div>
                        <div class="col-md-3"><label for="obu-protests">Protestas</label><input id="obu-protests" class="form-control" type="number" min="0" name="protests" required value="{{ old('protests', $currentMetrics?->protests) }}"></div>
                        <div class="col-md-3"><label for="obu-complaints">Denuncias</label><input id="obu-complaints" class="form-control" type="number" min="0" name="complaints" required value="{{ old('complaints', $currentMetrics?->complaints) }}"></div>
                        <div class="col-md-3"><label for="obu-data-date">Actualizado hasta</label><input id="obu-data-date" class="form-control" type="date" name="data_date" value="{{ old('data_date', $currentMetrics?->data_date?->format('Y-m-d')) }}"></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Guardar actualización</button>
                </form>
            </section>
        @else
            <section class="access-justice-card obu-metrics-editor">
                <header class="access-justice-card__header"><div><h2>Cifras de OBU</h2><p>Datos editoriales mostrados en el panel público.</p></div></header>
                <div class="row g-3"><div class="col-md-3"><span class="text-muted text-sm">Universidades monitoreadas</span><strong class="d-block">{{ $currentMetrics?->universities_monitored ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Protestas</span><strong class="d-block">{{ $currentMetrics?->protests ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Denuncias</span><strong class="d-block">{{ $currentMetrics?->complaints ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Actualizado hasta</span><strong class="d-block">{{ $currentMetrics?->data_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong></div></div>
            </section>
        @endcan

        @can('edit obu metrics')
            <section class="access-justice-card obu-editorial-admin-card">
                <header class="access-justice-card__header"><div><h2>Nota Mensual</h2><p>Publica una nota editorial sin eliminar versiones anteriores.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.monthly-note.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8"><label for="obu-note-title">Título</label><input id="obu-note-title" class="form-control" name="title" required value="{{ old('title', $latestMonthlyNote?->title) }}"></div>
                        <div class="col-md-4"><label for="obu-note-date">Mes / fecha</label><input id="obu-note-date" class="form-control" type="date" name="publication_date" required value="{{ old('publication_date', $latestMonthlyNote?->publication_date?->format('Y-m-d')) }}"></div>
                        <div class="col-12"><label for="obu-note-excerpt">Texto / resumen</label><textarea id="obu-note-excerpt" class="form-control" name="excerpt" rows="3" required>{{ old('excerpt', $latestMonthlyNote?->excerpt) }}</textarea></div>
                        <div class="col-md-8"><label for="obu-note-url">Enlace de lectura</label><input id="obu-note-url" class="form-control" type="url" name="url" value="{{ old('url', $latestMonthlyNote?->url) }}"></div>
                        <div class="col-md-4"><label for="obu-note-image">Imagen</label><input id="obu-note-image" class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">@if ($latestMonthlyNote?->image_path)<small class="text-muted">Imagen actual disponible.</small>@endif</div>
                    </div>
                    <label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked(old('is_published', $latestMonthlyNote?->is_published))> <span class="form-check-label">Publicar en el panel público</span></label>
                    <button class="btn btn-primary mt-3" type="submit">Guardar Nota Mensual</button>
                </form>
            </section>

            <section class="access-justice-card obu-editorial-admin-card">
                <header class="access-justice-card__header"><div><h2>Alerta Bimensual</h2><p>Publica una alerta con imagen y PDF descargable.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.bimonthly-alert.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label for="obu-alert-title">Título</label><input id="obu-alert-title" class="form-control" name="title" required value="{{ old('title', $latestBimonthlyAlert?->title) }}"></div>
                        <div class="col-md-3"><label for="obu-alert-start">Período desde</label><input id="obu-alert-start" class="form-control" type="date" name="period_start" required value="{{ old('period_start', $latestBimonthlyAlert?->period_start?->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label for="obu-alert-end">Período hasta</label><input id="obu-alert-end" class="form-control" type="date" name="period_end" required value="{{ old('period_end', $latestBimonthlyAlert?->period_end?->format('Y-m-d')) }}"></div>
                        <div class="col-12"><label for="obu-alert-excerpt">Texto / resumen</label><textarea id="obu-alert-excerpt" class="form-control" name="excerpt" rows="3" required>{{ old('excerpt', $latestBimonthlyAlert?->excerpt) }}</textarea></div>
                        <div class="col-md-6"><label for="obu-alert-url">Enlace de la alerta</label><input id="obu-alert-url" class="form-control" type="url" name="url" placeholder="https://..." value="{{ old('url', $latestBimonthlyAlert?->url) }}"></div>
                        <div class="col-md-6"><label for="obu-alert-image">Imagen</label><input id="obu-alert-image" class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">@if ($latestBimonthlyAlert?->image_path)<small class="text-muted">Imagen actual disponible.</small>@endif</div>
                        <div class="col-md-6"><label for="obu-alert-file">Archivo PDF</label><input id="obu-alert-file" class="form-control" type="file" name="file" accept=".pdf">@if ($latestBimonthlyAlert?->file_name)<small class="text-muted">Actual: {{ $latestBimonthlyAlert->file_name }} ({{ number_format(($latestBimonthlyAlert->file_size ?? 0) / 1024, 0) }} KB)</small>@endif</div>
                    </div>
                    <label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked(old('is_published', $latestBimonthlyAlert?->is_published))> <span class="form-check-label">Publicar en el panel público</span></label>
                    <button class="btn btn-primary mt-3" type="submit">Guardar Alerta Bimensual</button>
                </form>
            </section>

            <section class="access-justice-card obu-editorial-admin-card">
                <header class="access-justice-card__header"><div><h2>Datos del monitoreo</h2><p>Versión semestral editable sin sobrescribir el historial.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.monitoring-period.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-3"><label for="obu-period-start">Desde</label><input id="obu-period-start" class="form-control" type="date" name="period_start" required value="{{ old('period_start', $monitoringPeriod?->period_start?->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label for="obu-period-end">Hasta</label><input id="obu-period-end" class="form-control" type="date" name="period_end" required value="{{ old('period_end', $monitoringPeriod?->period_end?->format('Y-m-d')) }}"></div>
                        <div class="col-md-2"><label for="obu-analyzed">Informaciones</label><input id="obu-analyzed" class="form-control" type="number" min="0" name="analyzed_information" required value="{{ old('analyzed_information', $monitoringPeriod?->analyzed_information) }}"></div>
                        <div class="col-md-2"><label for="obu-period-protests">Protestas</label><input id="obu-period-protests" class="form-control" type="number" min="0" name="protests" required value="{{ old('protests', $monitoringPeriod?->protests) }}"></div>
                        <div class="col-md-2"><label for="obu-period-complaints">Denuncias</label><input id="obu-period-complaints" class="form-control" type="number" min="0" name="complaints" required value="{{ old('complaints', $monitoringPeriod?->complaints) }}"></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Guardar datos de monitoreo</button>
                </form>
            </section>
        @endcan

        <section class="access-justice-card obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de Notas Mensuales</h2><p>Versiones editoriales anteriores.</p></div></header>
            <div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Fecha</th><th>Título</th><th>Publicada</th><th>Modificado por</th></tr></thead><tbody>@forelse ($monthlyNotesHistory as $note)<tr><td>{{ $note->publication_date->format('d/m/Y') }}</td><td>{{ $note->title }}</td><td>{{ $note->is_published ? 'Sí' : 'No' }}</td><td>{{ $note->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="4">No hay notas registradas.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="access-justice-card obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de Alertas Bimensuales</h2><p>Versiones editoriales anteriores.</p></div></header>
            <div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Período</th><th>Título</th><th>PDF</th><th>Publicada</th><th>Modificado por</th></tr></thead><tbody>@forelse ($bimonthlyAlertsHistory as $alert)<tr><td>{{ $alert->period_start->format('d/m/Y') }} – {{ $alert->period_end->format('d/m/Y') }}</td><td>{{ $alert->title }}</td><td>{{ $alert->file_name }}</td><td>{{ $alert->is_published ? 'Sí' : 'No' }}</td><td>{{ $alert->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="5">No hay alertas registradas.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="access-justice-card obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de Datos del Monitoreo</h2><p>Versiones del período y sus cifras.</p></div></header>
            <div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Período</th><th>Informaciones</th><th>Protestas</th><th>Denuncias</th><th>Vigente desde</th><th>Vigente hasta</th></tr></thead><tbody>@forelse ($monitoringHistory as $period)<tr><td>{{ $period->period_start->format('d/m/Y') }} – {{ $period->period_end->format('d/m/Y') }}</td><td>{{ number_format($period->analyzed_information, 0, ',', '.') }}</td><td>{{ number_format($period->protests, 0, ',', '.') }}</td><td>{{ number_format($period->complaints, 0, ',', '.') }}</td><td>{{ $period->valid_from->format('d/m/Y H:i') }}</td><td>{{ $period->valid_until?->format('d/m/Y H:i') ?? 'Vigente' }}</td></tr>@empty<tr><td colspan="6">No hay períodos registrados.</td></tr>@endforelse</tbody></table></div>
        </section>

        <section class="access-justice-card obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Datasets históricos</h2><p>Datos estructurados del Monitor OBU, listos para futuras ediciones.</p></div></header>
            <div class="row g-3">@foreach ($datasetCounts as $datasetKey => $datasetCount)<div class="col-sm-6 col-lg-4"><span class="text-muted text-sm">{{ str_replace('_', ' ', $datasetKey) }}</span><strong class="d-block">{{ $datasetCount }} registros</strong></div>@endforeach</div>
            <small class="text-muted d-block mt-3">Años disponibles: {{ collect($datasetYears)->implode(', ') ?: 'Sin datos' }}</small>
        </section>

        <section class="access-justice-card access-justice-ranking obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de cifras de OBU</h2><p>Versiones editoriales, ordenadas por vigencia.</p></div></header>
            <div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Actualizado hasta</th><th>Universidades</th><th>Protestas</th><th>Denuncias</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr></thead><tbody>@forelse ($metricsHistory as $version)<tr><td>{{ $version->data_date?->format('d/m/Y') ?? 'Sin fecha' }}</td><td>{{ number_format($version->universities_monitored, 0, ',', '.') }}</td><td>{{ number_format($version->protests, 0, ',', '.') }}</td><td>{{ number_format($version->complaints, 0, ',', '.') }}</td><td>{{ $version->valid_from->format('d/m/Y H:i') }}</td><td>@if ($version->valid_until){{ $version->valid_until->format('d/m/Y H:i') }}@else<span class="ovfn-history-current-badge">Vigente</span>@endif</td><td>{{ $version->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="7">No hay historial registrado.</td></tr>@endforelse</tbody></table></div>
        </section>
    </main>

    @include('components.flash-toast', ['toasts' => [
        session('obu_metrics_success') ? ['type' => 'success', 'message' => session('obu_metrics_success')] : null,
        session('obu_metrics_info') ? ['type' => 'info', 'message' => session('obu_metrics_info')] : null,
        session('obu_note_success') ? ['type' => 'success', 'message' => session('obu_note_success')] : null,
        session('obu_alert_success') ? ['type' => 'success', 'message' => session('obu_alert_success')] : null,
        session('obu_monitoring_success') ? ['type' => 'success', 'message' => session('obu_monitoring_success')] : null,
        session('obu_monitoring_info') ? ['type' => 'info', 'message' => session('obu_monitoring_info')] : null,
    ]])
    <script type="application/json" id="obuAnalyticsData">@json(['visits' => $chart ?? [], 'origin' => $panelOrigin ?? []])</script>
    @vite('resources/js/admin-analytics.js')
</x-layouts::admin>
