<x-layouts::admin :title="'Analítica | OBU'">
    @php
        $summary = $summary ?? [];
        $currentMetrics = $currentMetrics;
        $rightsBreakdown = $currentMetrics?->rights_breakdown ?? [];
        $rightsBreakdownGroups = [
            [
                'type' => __('dashboard.complaints'),
                'classification' => __('dashboard.obu.economic_social_cultural_rights'),
                'items' => [
                    'fair_wages' => __('dashboard.obu.decent_wages'),
                    'infrastructure_damage' => __('dashboard.obu.infrastructure_damage'),
                    'student_welfare' => __('dashboard.obu.student_welfare'),
                ],
            ],
            [
                'type' => __('dashboard.complaints'),
                'classification' => __('dashboard.obu.political_civil_rights'),
                'items' => [
                    'university_autonomy' => __('dashboard.obu.university_autonomy'),
                    'freedom_of_expression' => __('dashboard.obu.freedom_of_expression'),
                    'public_affairs_participation' => __('dashboard.obu.public_affairs_participation'),
                ],
            ],
            [
                'type' => __('dashboard.obu.economic_rights_protests'),
                'classification' => null,
                'items' => [
                    'strike' => __('dashboard.obu.strike'),
                    'gathering' => __('dashboard.obu.gathering'),
                    'banner_protest' => __('dashboard.obu.banner_protest'),
                    'march' => __('dashboard.obu.march'),
                    'other' => __('dashboard.obu.other'),
                ],
            ],
        ];
        $period = today()->subDays(29)->locale('es')->isoFormat('D MMM YYYY').' – '.today()->locale('es')->isoFormat('D MMM YYYY');
    @endphp

    <main class="access-justice-dashboard obu-dashboard">
        <header class="access-justice-header">
            <div class="access-justice-header__copy">
                <span class="access-justice-header__accent"></span>
                <div class="col-12">
                    <h1>Observatorio de Universidades</h1>
                    <p>Resumen de analítica y rendimiento del módulo</p>
                </div>
            </div>
        </header>

        <section class="access-justice-kpis">
            @foreach ([['orange','fa-arrow-pointer','Clics desde Pulso',$summary['home_navigation_clicks'] ?? 0,'Navegación hacia el panel'],['blue','fa-eye','Visitas al portal Pulso Venezuela',$summary['portal_views'] ?? 0,'Total de visitas al portal'],['green','fa-window-maximize','Visitas al panel OBU',$summary['organization_views'] ?? 0,'Entradas al módulo'],['purple','fa-bullhorn','Clics en contenidos',$summary['content_clicks'] ?? 0,'Interacciones registradas']] as $kpi)
                <article class="analytics-kpi-card analytics-kpi-card--{{ $kpi[0] }}"><div class="analytics-kpi-main"><span class="analytics-kpi-icon"><i class="fa-solid {{ $kpi[1] }}"></i></span><div class="analytics-kpi-content"><p class="analytics-kpi-title">{{ $kpi[2] }}</p><strong class="analytics-kpi-value">{{ number_format($kpi[3]) }}</strong><small class="analytics-kpi-description">{{ $kpi[4] }}</small></div></div><svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="M0 28 C30 18 50 34 78 25 S125 14 154 25 S205 34 232 21 S280 15 320 20" /></svg></article>
            @endforeach
        </section>

        @php
            $contentClicksTotal = (int) ($summary['content_clicks'] ?? 0);
            $contentRanking = $contentRanking ?? collect();
            $contentTypes = collect($contentClicks ?? [])->except('total');
        @endphp

        <section class="access-justice-charts obu-analytics-charts">
            <article class="access-justice-card access-justice-chart-card"><header class="access-justice-card__header"><div><h2>Visitas por fecha</h2><p>Visitas al portal y al panel OBU</p></div><span class="access-justice-chart-period">Diaria</span></header><div class="access-justice-line-chart"><canvas id="obuVisitsChart" aria-label="Visitas por fecha" role="img"></canvas></div></article>
            <div class="access-justice-charts-side">
                <article class="access-justice-card access-justice-origin-card"><header class="access-justice-card__header"><div><h2>Origen de visitas al panel OBU</h2><p>Distribución de accesos registrados</p></div></header><div class="access-justice-origin-card__content"><div class="access-justice-donut"><canvas id="obuSourceChart" aria-label="Origen de visitas al panel OBU" role="img"></canvas></div><div class="access-justice-origin-legend"><div><span><i class="access-justice-dot access-justice-dot--orange"></i>Desde Pulso</span><strong>{{ number_format($panelOrigin['pulso'] ?? 0, 0, ',', '.') }}</strong></div><div><span><i class="access-justice-dot access-justice-dot--blue"></i>Acceso directo</span><strong>{{ number_format($panelOrigin['direct'] ?? 0, 0, ',', '.') }}</strong></div><div class="access-justice-origin-legend__total"><span>Total</span><strong>{{ number_format($panelOrigin['total'] ?? 0, 0, ',', '.') }}</strong></div></div></div></article>
            </div>
        </section>

        <section class="access-justice-card access-justice-ranking" aria-label="Ranking de contenidos OBU"><header class="access-justice-card__header access-justice-ranking__header"><div><h2>Ranking de contenidos</h2><p>Contenidos OBU según clics registrados</p></div></header><div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Contenido</th><th>Tipo</th><th class="text-end">Clics</th></tr></thead><tbody>@forelse ($contentRanking as $item)<tr><td>{{ $item['title'] }}</td><td>{{ $item['content_type'] }}</td><td class="text-end">{{ number_format($item['clicks'], 0, ',', '.') }}</td></tr>@empty<tr><td colspan="3" class="access-justice-empty-table">Todavía no hay clics registrados.</td></tr>@endforelse</tbody></table></div>@if (method_exists($contentRanking, 'links'))<div class="mt-3">{{ $contentRanking->links() }}</div>@endif</section>

        @can('edit obu metrics')
            <section class="access-justice-card obu-metrics-editor p-3">
                <header class="access-justice-card__header"><div><h2>Editar cifras de OBU</h2><p>Actualiza las cifras mostradas en el panel público.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.metrics.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-4"><label for="obu-analyzed-information">{{ __('dashboard.obu.analyzed_information') }}</label><input id="obu-analyzed-information" class="form-control @error('analyzed_information') is-invalid @enderror" type="number" min="0" name="analyzed_information" required value="{{ old('analyzed_information', $currentMetrics?->analyzed_information) }}">@error('analyzed_information')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label for="obu-protests">Protestas</label><input id="obu-protests" class="form-control @error('protests') is-invalid @enderror" type="number" min="0" name="protests" required value="{{ old('protests', $currentMetrics?->protests) }}">@error('protests')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label for="obu-complaints">Denuncias del período actual</label><input id="obu-complaints" class="form-control @error('complaints') is-invalid @enderror" type="number" min="0" name="complaints" required value="{{ old('complaints', $currentMetrics?->complaints) }}">@error('complaints')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12"><h3 class="h6 mb-0">Período de monitoreo</h3></div>
                        <div class="col-md-4"><label for="obu-period-start">Desde (mes y año)</label><input id="obu-period-start" class="form-control @error('period_start') is-invalid @enderror" type="month" name="period_start" required value="{{ old('period_start', $monitoringPeriod?->period_start?->format('Y-m')) }}">@error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label for="obu-period-end">Hasta (mes y año)</label><input id="obu-period-end" class="form-control @error('period_end') is-invalid @enderror" type="month" name="period_end" required value="{{ old('period_end', $monitoringPeriod?->period_end?->format('Y-m')) }}">@error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <button class="btn btn-primary mt-4" type="submit">Actualizar cifras</button>
                </form>
            </section>
        @else
            <section class="access-justice-card obu-metrics-editor">
                <header class="access-justice-card__header"><div><h2>Cifras de OBU</h2><p>Datos editoriales mostrados en el panel público.</p></div></header>
                <div class="row g-3"><div class="col-md-3"><span class="text-muted text-sm">Universidades monitoreadas</span><strong class="d-block">{{ $currentMetrics?->universities_monitored ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Protestas</span><strong class="d-block">{{ $currentMetrics?->protests ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Denuncias</span><strong class="d-block">{{ $currentMetrics?->complaints ?? 0 }}</strong></div><div class="col-md-3"><span class="text-muted text-sm">Período de monitoreo</span><strong class="d-block">{{ $monitoringPeriod?->period_start?->format('m/Y') ?? 'Sin período' }} – {{ $monitoringPeriod?->period_end?->format('m/Y') ?? '' }}</strong></div></div>
            </section>
        @endcan

        @php
                $complaintRows = collect(($datasetsForEditing['documented_complaints'] ?? []));
                $protestRows = collect(($datasetsForEditing['protest_types'] ?? []));
        @endphp

            <section class="access-justice-card obu-editorial-admin-card obu-simple-dataset-card">
                <header class="access-justice-card__header"><div><h2>Denuncias por derechos</h2><p>Actualiza las denuncias registradas según el tipo de derecho.</p></div></header>
                @can('edit obu metrics')
                <form method="POST" action="{{ route('admin.obu.dataset.update') }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="dataset_key" value="documented_complaints">
                    @foreach (['economic_social' => __('dashboard.obu.economic_social_cultural_rights'), 'civil_political' => __('dashboard.obu.political_civil_rights')] as $category => $categoryTitle)
                        <div class="obu-simple-dataset-group">
                            <h3>{{ $categoryTitle }}</h3>
                            <div class="obu-simple-dataset-grid">
                                @foreach ($complaintRows->where('category', $category) as $datasetRow)
                                    <article class="obu-simple-dataset-item">
                                        <h4>{{ $datasetRow->label }}</h4>
                                        <label for="obu-dataset-{{ $datasetRow->id }}">Valor</label>
                                        <input id="obu-dataset-{{ $datasetRow->id }}" class="form-control @error('values.'.$datasetRow->id.'.value') is-invalid @enderror" type="number" min="0" name="values[{{ $datasetRow->id }}][value]" required value="{{ old('values.'.$datasetRow->id.'.value', $datasetRow->value) }}">
                                        @error('values.'.$datasetRow->id.'.value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <input type="hidden" name="values[{{ $datasetRow->id }}][id]" value="{{ $datasetRow->id }}">
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <button class="btn btn-primary mt-4" type="submit">Guardar denuncias</button>
                </form>
                @else
                    <div class="obu-simple-dataset-readonly">
                        @foreach ($complaintRows as $datasetRow)
                            <article class="obu-simple-dataset-item"><h4>{{ $datasetRow->label }}</h4><span class="text-muted">Valor</span><strong class="d-block">{{ $datasetRow->value }}</strong></article>
                        @endforeach
                    </div>
                @endcan
            </section>

            <section class="access-justice-card obu-editorial-admin-card obu-simple-dataset-card">
                <header class="access-justice-card__header"><div><h2>Tipos de protesta</h2><p>Actualiza la cantidad de protestas registradas según su modalidad.</p></div></header>
                @can('edit obu metrics')
                <form method="POST" action="{{ route('admin.obu.dataset.update') }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="dataset_key" value="protest_types">
                    <div class="obu-simple-dataset-grid obu-simple-dataset-grid--protests">
                        @foreach ($protestRows as $datasetRow)
                            <article class="obu-simple-dataset-item">
                                <h4>{{ $datasetRow->label }}</h4>
                                <label for="obu-protest-dataset-{{ $datasetRow->id }}">Valor</label>
                                <input id="obu-protest-dataset-{{ $datasetRow->id }}" class="form-control @error('values.'.$datasetRow->id.'.value') is-invalid @enderror" type="number" min="0" name="values[{{ $datasetRow->id }}][value]" required value="{{ old('values.'.$datasetRow->id.'.value', $datasetRow->value) }}">
                                @error('values.'.$datasetRow->id.'.value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <input type="hidden" name="values[{{ $datasetRow->id }}][id]" value="{{ $datasetRow->id }}">
                            </article>
                        @endforeach
                    </div>
                    <button class="btn btn-primary mt-4" type="submit">Guardar tipos de protesta</button>
                </form>
                @else
                    <div class="obu-simple-dataset-grid obu-simple-dataset-grid--protests">
                        @foreach ($protestRows as $datasetRow)
                            <article class="obu-simple-dataset-item"><h4>{{ $datasetRow->label }}</h4><span class="text-muted">Valor</span><strong class="d-block">{{ $datasetRow->value }}</strong></article>
                        @endforeach
                    </div>
                @endcan
            </section>

        @can('edit obu metrics')
            <section class="access-justice-card obu-editorial-admin-card p-3 p-md-4">
                <header class="access-justice-card__header" style="margin-bottom: 24px;"><div><h2>Nota Mensual</h2><p>Publica una nota editorial sin eliminar versiones anteriores.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.monthly-note.store') }}" enctype="multipart/form-data">
                    @csrf
                    @php
                        $monthlyNoteImageUrl = null;
                        if (filled($latestMonthlyNote?->image_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($latestMonthlyNote->image_path)) {
                            $monthlyNoteImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($latestMonthlyNote->image_path);
                        } elseif (is_file(public_path('assets/img/nota-prensa-obu.jpg'))) {
                            $monthlyNoteImageUrl = asset('assets/img/nota-prensa-obu.jpg');
                        }
                    @endphp
                    <div class="obu-monthly-note-editor" style="margin-top: 0;">
                        <div class="obu-monthly-note-fields">
                            <div class="obu-monthly-note-field"><label for="obu-note-title">Título</label><input id="obu-note-title" class="form-control" name="title" required value="{{ old('title', $latestMonthlyNote?->title) }}"></div>
                            <div class="obu-monthly-note-field"><label for="obu-note-date">Mes / fecha</label><input id="obu-note-date" class="form-control" type="date" name="publication_date" required value="{{ old('publication_date', $latestMonthlyNote?->publication_date?->format('Y-m-d')) }}"></div>
                            <div class="obu-monthly-note-field"><label for="obu-note-excerpt">Texto / resumen</label><textarea id="obu-note-excerpt" class="form-control" name="excerpt" rows="5" required>{{ old('excerpt', $latestMonthlyNote?->excerpt) }}</textarea></div>
                            <div class="obu-monthly-note-field"><label for="obu-note-image">Imagen</label><input id="obu-note-image" class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small class="form-text">Selecciona una imagen para actualizar la previsualización.</small></div>
                            <div class="obu-monthly-note-field"><label for="obu-note-url">Enlace de lectura</label><input id="obu-note-url" class="form-control" type="url" name="url" value="{{ old('url', $latestMonthlyNote?->url) }}"></div>
                        </div>
                        <div class="obu-monthly-note-preview-column" style="align-self: start; padding: 16px; background: #f8fafc; border: 1px solid #e5e9f0; border-radius: 12px;">
                            <h3 class="obu-monthly-note-preview-title">Vista previa de la imagen</h3>
                            <div class="obu-monthly-note-preview">
                                @if ($monthlyNoteImageUrl)
                                    <img id="obu-note-preview-image" src="{{ $monthlyNoteImageUrl }}" alt="Imagen actual de la Nota Mensual">
                                    <span id="obu-note-preview-placeholder" class="d-none">Sin imagen cargada</span>
                                @else
                                    <img id="obu-note-preview-image" class="d-none" src="" alt="">
                                    <span id="obu-note-preview-placeholder">Sin imagen cargada</span>
                                @endif
                            </div>
                            <div class="obu-monthly-note-preview-help" style="margin-top: 12px; padding: 12px; border-radius: 8px;"><i class="bi bi-image" aria-hidden="true"></i><span><strong>Tama&ntilde;o recomendado: 1200 &times; 680 px</strong><small>La imagen se ajustar&aacute; autom&aacute;ticamente al formato del panel.</small></span></div>
                        </div>
                    </div>
                    <label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked(old('is_published', $latestMonthlyNote?->is_published))> <span class="form-check-label">Publicar en el panel público</span></label>
                    <button class="btn btn-primary mt-3" style="margin-top: 20px !important;" type="submit">Guardar Nota Mensual</button>
                </form>
            </section>

            <section class="access-justice-card obu-editorial-admin-card p-3 p-md-4">
                <header class="access-justice-card__header" style="margin-bottom: 24px;"><div><h2>Alerta Bimensual</h2><p>Publica una alerta con imagen y PDF descargable.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.bimonthly-alert.store') }}" enctype="multipart/form-data">
                    @csrf
                    @php
                        $bimonthlyAlertImageUrl = null;
                        if (filled($latestBimonthlyAlert?->image_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($latestBimonthlyAlert->image_path)) {
                            $bimonthlyAlertImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($latestBimonthlyAlert->image_path);
                        }
                    @endphp
                    <div class="obu-monthly-note-editor obu-bimonthly-alert-editor" style="margin-top: 0;">
                        <div class="obu-monthly-note-fields">
                            <div class="obu-monthly-note-field"><label for="obu-alert-title">Título</label><input id="obu-alert-title" class="form-control" name="title" required value="{{ old('title', $latestBimonthlyAlert?->title) }}"></div>
                            <div class="obu-bimonthly-alert-periods">
                                <div class="obu-monthly-note-field"><label for="obu-alert-start">Período desde</label><input id="obu-alert-start" class="form-control" type="date" name="period_start" required value="{{ old('period_start', $latestBimonthlyAlert?->period_start?->format('Y-m-d')) }}"></div>
                                <div class="obu-monthly-note-field"><label for="obu-alert-end">Período hasta</label><input id="obu-alert-end" class="form-control" type="date" name="period_end" required value="{{ old('period_end', $latestBimonthlyAlert?->period_end?->format('Y-m-d')) }}"></div>
                            </div>
                            <div class="obu-monthly-note-field"><label for="obu-alert-excerpt">Texto / resumen</label><textarea id="obu-alert-excerpt" class="form-control" name="excerpt" rows="4" required>{{ old('excerpt', $latestBimonthlyAlert?->excerpt) }}</textarea></div>
                            <div class="obu-monthly-note-field"><label for="obu-alert-image">Imagen</label><input id="obu-alert-image" class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"><small class="form-text">Selecciona una imagen para actualizar la previsualización. Formatos permitidos: JPG, PNG, WEBP.</small></div>
                            <div class="obu-monthly-note-field"><label for="obu-alert-url">Enlace de la alerta</label><input id="obu-alert-url" class="form-control" type="url" name="url" placeholder="https://..." value="{{ old('url', $latestBimonthlyAlert?->url) }}"></div>
                            <div class="obu-monthly-note-field"><label for="obu-alert-file">Archivo PDF</label><input id="obu-alert-file" class="form-control" type="file" name="file" accept=".pdf">@if ($latestBimonthlyAlert?->file_name)<small class="form-text">PDF actual disponible: {{ $latestBimonthlyAlert->file_name }} ({{ number_format(($latestBimonthlyAlert->file_size ?? 0) / 1024, 0) }} KB)</small>@endif</div>
                        </div>
                        <div class="obu-monthly-note-preview-column" style="align-self: start; padding: 16px; background: #f8fafc; border: 1px solid #e5e9f0; border-radius: 12px;">
                            <h3 class="obu-monthly-note-preview-title">Vista previa de la imagen</h3>
                            <div class="obu-monthly-note-preview">
                                @if ($bimonthlyAlertImageUrl)
                                    <img id="obu-alert-preview-image" src="{{ $bimonthlyAlertImageUrl }}" alt="Imagen actual de la Alerta Bimensual">
                                    <span id="obu-alert-preview-placeholder" class="d-none">Sin imagen cargada</span>
                                @else
                                    <img id="obu-alert-preview-image" class="d-none" src="" alt="">
                                    <span id="obu-alert-preview-placeholder">Sin imagen cargada</span>
                                @endif
                            </div>
                            <div class="obu-monthly-note-preview-help" style="margin-top: 12px; padding: 12px; border-radius: 8px;"><i class="bi bi-image" aria-hidden="true"></i><span><strong>Tamaño recomendado: 1200 &times; 680 px</strong><small>La imagen se ajustará automáticamente al formato del panel.</small></span></div>
                        </div>
                    </div>
                    <label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked(old('is_published', $latestBimonthlyAlert?->is_published))> <span class="form-check-label">Publicar en el panel público</span></label>
                    <button class="btn btn-primary mt-3" style="margin-top: 20px !important;" type="submit">Guardar Alerta Bimensual</button>
                </form>
            </section>

            {{-- Datos del monitoreo: ahora se gestiona desde Editar cifras de OBU. --}}
            {{--
            <section class="access-justice-card obu-editorial-admin-card">
                <header class="access-justice-card__header"><div><h2>Datos del monitoreo</h2><p>Versión semestral editable sin sobrescribir el historial.</p></div></header>
                <form method="POST" action="{{ route('admin.obu.monitoring-period.update') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-3"><label for="obu-monitoring-period-start">Desde</label><input id="obu-monitoring-period-start" class="form-control" type="date" name="period_start" required value="{{ old('period_start', $monitoringPeriod?->period_start?->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label for="obu-monitoring-period-end">Hasta</label><input id="obu-monitoring-period-end" class="form-control" type="date" name="period_end" required value="{{ old('period_end', $monitoringPeriod?->period_end?->format('Y-m-d')) }}"></div>
                        <div class="col-md-2"><label for="obu-analyzed">{{ __('dashboard.obu.analyzed_information') }}</label><input id="obu-analyzed" class="form-control" type="number" min="0" name="analyzed_information" required value="{{ old('analyzed_information', $monitoringPeriod?->analyzed_information) }}"></div>
                        <div class="col-md-2"><label for="obu-period-protests">Protestas</label><input id="obu-period-protests" class="form-control" type="number" min="0" name="protests" required value="{{ old('protests', $monitoringPeriod?->protests) }}"></div>
                        <div class="col-md-2"><label for="obu-period-complaints">Denuncias</label><input id="obu-period-complaints" class="form-control" type="number" min="0" name="complaints" required value="{{ old('complaints', $monitoringPeriod?->complaints) }}"></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Guardar datos de monitoreo</button>
                </form>
            </section>
            --}}
        @endcan

        @can('edit obu metrics')
            <section class="access-justice-card obu-editorial-admin-card">
                <header class="access-justice-card__header"><div><h2>Datasets editables</h2><p>Actualiza valores, categorías, etiquetas y orden sin eliminar registros.</p></div></header>
                @foreach (($datasetsForEditing ?? []) as $datasetKey => $datasetRows)
                    @if (in_array($datasetKey, ['documented_complaints', 'protest_types'], true)) @continue @endif
                    <form method="POST" action="{{ route('admin.obu.dataset.update') }}" class="obu-dataset-editor mb-4">
                        @csrf @method('PATCH')
                        <input type="hidden" name="dataset_key" value="{{ $datasetKey }}">
                        <h3 class="h6 text-uppercase text-muted mb-2">{{ str_replace('_', ' ', $datasetKey) }}</h3>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead><tr><th>Año</th><th>Categoría</th><th>Subgrupo</th><th>Etiqueta</th><th>Valor</th><th>%</th><th>Orden</th></tr></thead>
                                <tbody>
                                    @forelse ($datasetRows as $datasetRow)
                                        <tr>
                                            <td><input class="form-control form-control-sm" type="number" min="1900" max="2200" name="values[{{ $datasetRow->id }}][period_year]" value="{{ $datasetRow->period_year }}"></td>
                                            <td><input class="form-control form-control-sm" name="values[{{ $datasetRow->id }}][category]" maxlength="120" required value="{{ $datasetRow->category }}"></td>
                                            <td><input class="form-control form-control-sm" name="values[{{ $datasetRow->id }}][subgroup]" maxlength="80" value="{{ $datasetRow->subgroup }}"></td>
                                            <td><input class="form-control form-control-sm" name="values[{{ $datasetRow->id }}][label]" maxlength="255" required value="{{ $datasetRow->label }}"></td>
                                            <td><input class="form-control form-control-sm" type="number" min="0" name="values[{{ $datasetRow->id }}][value]" required value="{{ $datasetRow->value }}"></td>
                                            <td><input class="form-control form-control-sm" type="number" min="0" max="100" step="0.01" name="values[{{ $datasetRow->id }}][percentage]" value="{{ $datasetRow->percentage }}"></td>
                                            <td><input class="form-control form-control-sm" type="number" min="0" name="values[{{ $datasetRow->id }}][sort_order]" required value="{{ $datasetRow->sort_order }}"></td>
                                            <input type="hidden" name="values[{{ $datasetRow->id }}][id]" value="{{ $datasetRow->id }}">
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-muted">No hay registros para este dataset.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($datasetRows->isNotEmpty())<button class="btn btn-primary btn-sm" type="submit">Guardar {{ str_replace('_', ' ', $datasetKey) }}</button>@endif
                    </form>
                @endforeach
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

        <section class="access-justice-card obu-history">
            <header class="access-justice-card__header access-justice-ranking__header"><div><h2>Historial de datasets</h2><p>Últimos cambios conservados con vigencia y usuario.</p></div></header>
            <div class="access-justice-table-wrap alert-ranking-table-scroll"><table class="table mb-0"><thead><tr><th>Dataset</th><th>Etiqueta</th><th>Valor</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr></thead><tbody>@forelse (($datasetVersionHistory ?? []) as $version)<tr><td>{{ str_replace('_', ' ', $version->dataset_key) }}</td><td>{{ $version->label }}</td><td>{{ $version->value }}</td><td>{{ $version->valid_from->format('d/m/Y H:i') }}</td><td>{{ $version->valid_until->format('d/m/Y H:i') }}</td><td>{{ $version->user?->name ?? 'Sistema' }}</td></tr>@empty<tr><td colspan="6">No hay versiones de datasets registradas.</td></tr>@endforelse</tbody></table></div>
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
        session('obu_dataset_success') ? ['type' => 'success', 'message' => session('obu_dataset_success')] : null,
    ]])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bindImagePreview = (inputId, previewId, placeholderId) => {
                const imageInput = document.getElementById(inputId);
                const previewImage = document.getElementById(previewId);
                const previewPlaceholder = document.getElementById(placeholderId);
                let previewObjectUrl = null;

                if (!imageInput || !previewImage || !previewPlaceholder) return;

                imageInput.addEventListener('change', () => {
                    const [file] = imageInput.files || [];
                    if (!file) return;

                    if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
                    previewObjectUrl = URL.createObjectURL(file);
                    previewImage.src = previewObjectUrl;
                    previewImage.alt = file.name;
                    previewImage.classList.remove('d-none');
                    previewPlaceholder.classList.add('d-none');
                });
            };

            bindImagePreview('obu-note-image', 'obu-note-preview-image', 'obu-note-preview-placeholder');
            bindImagePreview('obu-alert-image', 'obu-alert-preview-image', 'obu-alert-preview-placeholder');
        });
    </script>
    <script type="application/json" id="obuAnalyticsData">@json(['chart' => $chart ?? [], 'origin' => $panelOrigin ?? [], 'content' => $contentClicksChart ?? []])</script>
    @vite('resources/js/admin-analytics.js')
</x-layouts::admin>
