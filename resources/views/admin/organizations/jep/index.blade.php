<x-layouts::admin :title="'Analítica | JEP'">
    @php
        $snapshot = $currentMetrics;
        $groups = $snapshot?->vulnerableGroups ?? collect();
        $centers = $snapshot?->detentionCenters ?? collect();
        $deathDistribution = $snapshot?->deathCustodyDistribution ?? collect();
        $deathDistributionByKey = $deathDistribution->keyBy('category_key');
        $value = fn (string $key, $fallback = '') => old($key, $snapshot?->{$key} ?? $fallback);
        $history = collect($metricsHistory)->sortByDesc('valid_from')->values();
        $normalizeHistoryRelation = function ($value, string $relation): array {
            $fields = match ($relation) {
                'vulnerableGroups' => ['group_key', 'label', 'value', 'sort_order'],
                'detentionCenters' => ['name', 'value', 'sort_order'],
                default => ['category_key', 'label', 'value', 'sort_order'],
            };
            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                $items = [$value];
            } elseif ($value instanceof \Illuminate\Support\Collection) {
                $items = $value->all();
            } elseif (is_array($value)) {
                $items = array_is_list($value) ? $value : [$value];
            } elseif ($value === null) {
                $items = [];
            } else {
                $items = [(array) $value];
            }

            return collect($items)
                ->map(function ($item) use ($fields) {
                    if ($item instanceof \Illuminate\Database\Eloquent\Model) {
                        $item = $item->toArray();
                    } elseif ($item instanceof \Illuminate\Support\Collection) {
                        $item = $item->all();
                    } elseif (! is_array($item)) {
                        $item = (array) $item;
                    }

                    return collect($item)
                        ->only($fields)
                        ->map(fn ($field) => (string) $field)
                        ->all();
                })
                ->sortBy(fn (array $item) => json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->values()
                ->all();
        };
        $historyFor = function (array $fields, ?string $relation = null, string $pageName = 'jep_history_page') use ($history, $normalizeHistoryRelation) {
            $filtered = $history->filter(function ($version, $index) use ($fields, $relation, $history, $normalizeHistoryRelation) {
                $previous = $history->get($index + 1);
                if (! $previous) return true;
                foreach ($fields as $field) {
                    if ((string) $version->{$field} !== (string) $previous->{$field}) return true;
                }
                if ($relation) {
                    $currentItems = $normalizeHistoryRelation($version->{$relation}, $relation);
                    $previousItems = $normalizeHistoryRelation($previous->{$relation}, $relation);
                    return $currentItems !== $previousItems;
                }
                return false;
            })->values();
            $perPage = 5;
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);
            return new \Illuminate\Pagination\LengthAwarePaginator($filtered->forPage($currentPage, $perPage)->values(), $filtered->count(), $perPage, $currentPage, ['path' => request()->url(), 'pageName' => $pageName]);
        };
        $mainHistory = $historyFor(['total_political_prisoners', 'total_political_prisoners_trend', 'women', 'women_trend', 'seriously_ill', 'seriously_ill_trend', 'foreign_or_dual_nationality', 'foreign_or_dual_nationality_trend', 'releases', 'releases_trend', 'releases_period_start_month', 'releases_period_start_day', 'releases_period_start_year', 'releases_period_end_month', 'releases_period_end_day', 'releases_period_end_year'], null, 'main_history_page');
        $featuredHistory = $historyFor(['featured_indicator_title', 'featured_indicator_text', 'featured_indicator_instagram_url', 'featured_indicator_x_url', 'featured_indicator_read_more_url', 'featured_indicator_image_path'], null, 'featured_history_page');
        $deathHistory = $historyFor(['deaths_period_start_day', 'deaths_period_start_month', 'deaths_period_start_year', 'deaths_period_end_day', 'deaths_period_end_month', 'deaths_period_end_year'], 'deathCustodyDistribution', 'death_history_page');
        $alertHistory = $historyFor(['monthly_alert_title', 'monthly_alert_excerpt', 'monthly_alert_x_url'], null, 'alert_history_page');
        $indicatorHistory = $historyFor(['active_retired_officials', 'new_detentions', 'missing_location'], null, 'indicators_history_page');
        $groupHistory = $historyFor([], 'vulnerableGroups', 'groups_history_page');
        $centerHistory = $historyFor([], 'detentionCenters', 'centers_history_page');
        $contentBreakdown = collect($contentClicks ?? [])->except('total');
        $contentTypeLabels = [
            'featured_instagram' => 'Instagram del destacado',
            'featured_x' => 'X del destacado',
            'featured_read_more' => 'Leer más del destacado',
            'monthly_alert' => 'Alerta del mes',
            'monthly_alert_x' => 'Publicación en X de la alerta',
            'criteria' => 'Criterios de contabilización',
            'instagram' => 'Instagram del destacado',
            'x_post' => 'X del destacado',
        ];
        $friendlyContentBreakdown = $contentBreakdown->mapWithKeys(
            fn ($total, $type) => [$contentTypeLabels[$type] ?? str($type)->replace('_', ' ')->title() => $total]
        );
        $sparklinePath = function (array $values): string {
            $values = array_map('intval', $values);
            $count = count($values);
            $max = max(1, ...$values);

            return collect($values)->map(function (int $value, int $index) use ($count, $max): string {
                $x = $count > 1 ? ($index / ($count - 1)) * 320 : 160;
                $y = 38 - (($value / $max) * 32);

                return ($index === 0 ? 'M' : 'L').number_format($x, 2, '.', '').' '.number_format($y, 2, '.', '');
            })->implode(' ');
        };
        $analyticsKpis = [
            ['orange', 'fa-arrow-pointer', 'Clics desde Pulso', $summary['home_navigation_clicks'] ?? 0, 'Navegación hacia el panel', $visitsChart['labels'] ?? [], $visitsChart['organization'] ?? []],
            ['blue', 'fa-eye', 'Visitas al portal Pulso Venezuela', $summary['portal_views'] ?? 0, 'Total de visitas al portal', $visitsChart['labels'] ?? [], $visitsChart['portal'] ?? []],
            ['green', 'fa-window-maximize', 'Visitas al panel JEP', $summary['organization_views'] ?? 0, 'Entradas al panel del módulo', $visitsChart['labels'] ?? [], $visitsChart['organization'] ?? []],
            ['purple', 'fa-bullhorn', 'Clics en contenidos', $summary['content_clicks'] ?? 0, $contentBreakdown->isEmpty() ? 'Sin clics de contenido trackeados' : $contentBreakdown->map(fn ($total, $type) => str($type)->replace('_', ' ')->title().': '.$total)->implode(' · '), $contentClicksChart['labels'] ?? [], $contentClicksChart['total'] ?? []],
        ];
        $analyticsKpis[3][4] = $friendlyContentBreakdown->isEmpty()
            ? 'Sin clics de contenido trackeados'
            : $friendlyContentBreakdown->map(fn ($total, $type) => $type.': '.$total)->implode(' · ');
        $deathFields = [
            ['key' => 'home_arrest', 'label' => 'Arresto domiciliario', 'sort_order' => 1],
            ['key' => 'detention_centers', 'label' => 'En centros de reclusión', 'sort_order' => 2],
            ['key' => 'hospitals', 'label' => 'Hospitales', 'sort_order' => 3],
        ];
        $releaseDate = function (string $part) use ($snapshot): string {
            $month = $snapshot?->{"releases_period_{$part}_month"};
            $day = $snapshot?->{"releases_period_{$part}_day"};
            $year = $snapshot?->{"releases_period_{$part}_year"};

            return $month && $day && $year ? sprintf('%04d-%02d-%02d', $year, $month, $day) : '';
        };
        $deathPeriod = function (string $part) use ($snapshot): string {
            $month = $snapshot?->{"deaths_period_{$part}_month"};
            $day = $snapshot?->{"deaths_period_{$part}_day"};
            $year = $snapshot?->{"deaths_period_{$part}_year"};

            if (! $month || ! $year) return '';
            $day ??= $part === 'start' ? 1 : \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->day;
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        };
    @endphp

    <main class="access-justice-dashboard jep-admin-dashboard">
        <header class="access-justice-header">
            <div class="access-justice-header__copy">
                <span class="access-justice-header__accent"></span>
                <div class="col-12">
                    <h1>Justicia, Encuentro y Perdón</h1>
                    <p>Resumen de analítica y métricas editoriales JEP</p>
                </div>
            </div>
        </header>

        <section class="access-justice-kpis">
            @foreach ($analyticsKpis as [$tone, $icon, $title, $total, $description, $labels, $series])
                <article class="analytics-kpi-card analytics-kpi-card--{{ $tone }}">
                    <div class="analytics-kpi-main">
                        <span class="analytics-kpi-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <div class="analytics-kpi-content"><p class="analytics-kpi-title">{{ $title }}</p><strong class="analytics-kpi-value">{{ number_format($total) }}</strong><small class="analytics-kpi-description">{{ $description }}</small></div>
                    </div>
                    <svg class="analytics-kpi-sparkline" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true"><path d="{{ $sparklinePath($series) }}" /></svg>
                </article>
            @endforeach
        </section>

        @can('edit jep metrics')
            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Cifras principales</h2><p>Actualiza las cifras principales mostradas en el panel público JEP.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.main-metrics.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-main-metrics-grid">
                        @foreach ([['total_political_prisoners','total_political_prisoners_trend','Total de presos políticos'],['women','women_trend','Mujeres'],['seriously_ill','seriously_ill_trend','Enfermos graves'],['foreign_or_dual_nationality','foreign_or_dual_nationality_trend','Extranjeros y doble nacionalidad']] as [$name,$trendName,$label])
                            <fieldset class="jep-main-metric-group">
                                <legend>{{ $label }}</legend>
                                <div class="jep-main-metric-group__fields"><div><label for="jep-{{ $name }}">Valor</label><input id="jep-{{ $name }}" class="form-control" type="number" min="0" name="{{ $name }}" required value="{{ $value($name, 0) }}"></div><div><label for="jep-{{ $trendName }}">Tendencia (%)</label><input id="jep-{{ $trendName }}" class="form-control" type="number" step="0.1" name="{{ $trendName }}" placeholder="Ej. 5.2 / -3.1" value="{{ $value($trendName) }}"></div></div>
                            </fieldset>
                        @endforeach
                        <fieldset class="jep-main-metric-group jep-main-metric-group--releases">
                            <legend>Excarcelaciones</legend>
                            <div class="jep-main-metric-group__fields"><div><label for="jep-releases">Valor</label><input id="jep-releases" class="form-control" type="number" min="0" name="releases" required value="{{ $value('releases', 0) }}"></div><div><label for="jep-releases-trend">Tendencia (%)</label><input id="jep-releases-trend" class="form-control" type="number" step="0.1" name="releases_trend" placeholder="Ej. 5.2 / -3.1" value="{{ $value('releases_trend') }}"></div></div>
                            <div class="jep-release-period"><span>Período de excarcelaciones</span><div><label for="jep-releases-start-date">Desde</label><input id="jep-releases-start-date" class="form-control" type="date" name="releases_period_start_date" value="{{ old('releases_period_start_date', $releaseDate('start')) }}"></div><div><label for="jep-releases-end-date">Hasta</label><input id="jep-releases-end-date" class="form-control" type="date" name="releases_period_end_date" value="{{ old('releases_period_end_date', $releaseDate('end')) }}"></div></div>
                        </fieldset>
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar cifras principales</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $mainHistory, 'kind' => 'main'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Indicadores</h2><p>Actualiza los indicadores complementarios del panel JEP.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.indicators.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-indicators-first-row">
                        @foreach ([['active_retired_officials','Funcionarios activos y retirados'],['new_detentions','Nuevas detenciones o reportadas durante el período'],['missing_location','Presos políticos sin información sobre su paradero']] as [$name,$label])
                            <fieldset class="jep-main-metric-group jep-indicator-metric-group"><legend>{{ $label }}</legend><div class="jep-main-metric-group__fields jep-main-metric-group__fields--single"><div><label for="jep-{{ $name }}">Valor</label><input id="jep-{{ $name }}" class="form-control" type="number" min="0" name="{{ $name }}" value="{{ $value($name, 0) }}" required></div></div></fieldset>
                        @endforeach
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar indicadores</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $indicatorHistory, 'kind' => 'indicators'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Fallecidos en custodia</h2><p>Actualiza la distribución y el período que alimentan el donut público.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.death-custody.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-records-grid">
                        @foreach ($deathFields as $index => $field)
                            @php($distribution = $deathDistributionByKey->get($field['key']))
                            <fieldset class="jep-main-metric-group jep-record-card">
                                <legend>{{ $field['label'] }}</legend>
                                <input type="hidden" name="death_custody_distribution[{{ $index }}][category_key]" value="{{ $field['key'] }}">
                                <input type="hidden" name="death_custody_distribution[{{ $index }}][sort_order]" value="{{ $field['sort_order'] }}">
                                <input type="hidden" name="death_custody_distribution[{{ $index }}][label]" value="{{ $field['label'] }}">
                                <div class="jep-record-card__fields"><div><label for="jep-death-{{ $field['key'] }}">Valor</label><input id="jep-death-{{ $field['key'] }}" class="form-control" type="number" min="0" name="death_custody_distribution[{{ $index }}][value]" value="{{ old("death_custody_distribution.$index.value", $distribution?->value ?? 0) }}" required></div></div>
                            </fieldset>
                        @endforeach
                    </div>
                    <div class="jep-indicators-period-row">
                        <fieldset class="jep-main-metric-group jep-indicator-metric-group"><legend>Período de fallecidos en custodia</legend><div class="jep-indicator-period-fields"><div><label for="jep-deaths-period-start">Desde</label><input id="jep-deaths-period-start" class="form-control" type="date" name="deaths_period_start" value="{{ old('deaths_period_start', $deathPeriod('start')) }}"></div><div><label for="jep-deaths-period-end">Hasta</label><input id="jep-deaths-period-end" class="form-control" type="date" name="deaths_period_end" value="{{ old('deaths_period_end', $deathPeriod('end')) }}"></div></div><small class="jep-admin-form__summary">Total actual: {{ $deathDistributionByKey->sum('value') }}</small></fieldset>
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar fallecidos en custodia</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $deathHistory, 'kind' => 'death'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Grupos vulnerables</h2><p>Administra los grupos y sus valores actuales.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.vulnerable-groups.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-records-grid">
                        @foreach (['sindicalistas' => 'Sindicalistas', 'organizaciones_politicas' => 'Organizaciones políticas', 'sociedad_civil' => 'Sociedad civil'] as $groupKey => $groupLabel)
                            @php($group = $groups->firstWhere('group_key', $groupKey))
                            <fieldset class="jep-main-metric-group jep-record-card">
                                <legend>{{ $groupLabel }}</legend>
                                <div class="jep-record-card__fields">
                                    <div><label for="jep-group-value-{{ $groupKey }}">Valor</label><input id="jep-group-value-{{ $groupKey }}" class="form-control" type="number" min="0" name="groups[{{ $groupKey }}]" value="{{ old("groups.$groupKey", $group?->value ?? 0) }}" required></div>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar grupos vulnerables</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $groupHistory, 'kind' => 'groups'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Centros de detención</h2><p>Actualiza nombres, valores y orden del ranking público.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.detention-centers.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-records-grid">
                        @foreach ($centers as $index => $center)
                            <fieldset class="jep-main-metric-group jep-record-card">
                                <legend>Centro de detención</legend>
                                <input type="hidden" name="centers[{{ $index }}][sort_order]" value="{{ $center->sort_order }}">
                                <div class="jep-record-card__fields">
                                    <div><label for="jep-center-name-{{ $index }}">Nombre</label><input id="jep-center-name-{{ $index }}" class="form-control" name="centers[{{ $index }}][name]" value="{{ old("centers.$index.name", $center->name) }}" required></div>
                                    <div><label for="jep-center-value-{{ $index }}">Valor</label><input id="jep-center-value-{{ $index }}" class="form-control" type="number" min="0" name="centers[{{ $index }}][value]" value="{{ old("centers.$index.value", $center->value) }}" required></div>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar centros de detención</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $centerHistory, 'kind' => 'centers'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>Alerta del mes</h2><p>Actualiza el texto editorial y conserva la referencia al post de X.</p></div></header>
                <form method="POST" action="{{ route('admin.jep.monthly-alert.update') }}" class="jep-admin-form">
                    @csrf @method('PATCH')
                    <div class="jep-monthly-alert-grid">
                        <div class="jep-monthly-alert-field">
                            <label for="jep-alert-title">Título</label>
                            <input id="jep-alert-title" class="form-control" type="text" name="monthly_alert_title" value="{{ old('monthly_alert_title', $snapshot?->monthly_alert_title ?: __('dashboard.jep_page.indicators.monthly_alert')) }}">
                        </div>
                        <div class="jep-monthly-alert-field">
                            <label for="jep-alert-url">URL del post de X</label>
                            <div class="jep-monthly-alert-fetch">
                                <input id="jep-monthly-alert-url" class="form-control mt-0" type="url" name="monthly_alert_x_url" placeholder="https://x.com/usuario/status/123456789" value="{{ old('monthly_alert_x_url', $snapshot?->monthly_alert_x_url) }}">
                                <button id="jep-fetch-monthly-alert" class="btn btn-outline-primary" type="button" data-endpoint="{{ route('admin.jep.monthly-alert.fetch-x-post') }}">
                                    Buscar publicación
                                </button>
                            </div>
                            <small id="jep-fetch-monthly-alert-status" class="form-text" role="status"></small>
                        </div>
                        <div class="jep-monthly-alert-field jep-monthly-alert-field--full">
                            <label for="jep-alert-excerpt">Texto / resumen</label>
                            <textarea id="jep-alert-excerpt" class="form-control" name="monthly_alert_excerpt" rows="6" maxlength="10000">{{ old('monthly_alert_excerpt', $snapshot?->monthly_alert_excerpt ?: __('dashboard.jep_page.indicators.alert_text')) }}</textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">Actualizar alerta del mes</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $alertHistory, 'kind' => 'alert'])

            <section class="jep-admin-section-card">
                <header class="jep-admin-section-card__header"><div><h2>{{ __('dashboard.jep_admin.featured.section_title') }}</h2><p>{{ __('dashboard.jep_admin.featured.description') }}</p></div></header>
                <form method="POST" action="{{ route('admin.jep.featured-indicator.update') }}" enctype="multipart/form-data" class="jep-admin-form">
                    @csrf @method('PATCH')
                    @if ($errors->hasAny(['featured_indicator_title', 'featured_indicator_text', 'featured_indicator_image', 'featured_indicator_instagram_url', 'featured_indicator_x_url', 'featured_indicator_read_more_url']))
                        <div class="alert alert-danger" role="alert">
                            @foreach (['featured_indicator_title', 'featured_indicator_text', 'featured_indicator_image', 'featured_indicator_instagram_url', 'featured_indicator_x_url', 'featured_indicator_read_more_url'] as $field)
                                @error($field)<div>{{ $message }}</div>@enderror
                            @endforeach
                        </div>
                    @endif
                    <div class="jep-featured-admin-top">
                        <div class="jep-featured-admin-fields">
                            <div class="jep-featured-admin-field"><label for="jep-featured-title">{{ __('dashboard.jep_admin.featured.title') }}</label><input id="jep-featured-title" class="form-control" type="text" name="featured_indicator_title" maxlength="255" value="{{ old('featured_indicator_title', $snapshot?->featured_indicator_title ?: __('dashboard.featured_title')) }}"></div>
                            <div class="jep-featured-admin-field"><label for="jep-featured-text">{{ __('dashboard.jep_admin.featured.text') }}</label><textarea id="jep-featured-text" class="form-control" name="featured_indicator_text" rows="5" maxlength="10000">{{ old('featured_indicator_text', $snapshot?->featured_indicator_text ?: __('dashboard.featured_analysis_jep')) }}</textarea></div>
                            <div class="jep-featured-admin-field"><label for="jep-featured-image">{{ __('dashboard.jep_admin.featured.image') }}</label><input id="jep-featured-image" class="form-control" type="file" name="featured_indicator_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small class="form-text">{{ __('dashboard.jep_admin.featured.image_help') }}</small></div>
                        </div>
                        <div class="jep-featured-admin-preview-column">
                            <div id="jep-featured-preview" class="jep-featured-admin-preview">
                                @if (filled($snapshot?->featured_indicator_image_path))
                                    <img id="jep-featured-preview-image" src="{{ \Illuminate\Support\Facades\Storage::url($snapshot->featured_indicator_image_path) }}" alt="{{ __('dashboard.jep_admin.featured.current_image') }}">
                                    <span id="jep-featured-preview-placeholder" class="d-none">{{ __('dashboard.jep_admin.featured.preview_placeholder') }}</span>
                                @else
                                    <img id="jep-featured-preview-image" class="d-none" src="" alt="">
                                    <span id="jep-featured-preview-placeholder">{{ __('dashboard.jep_admin.featured.preview_placeholder') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="jep-featured-admin-url-grid">
                        <div class="jep-featured-admin-field"><label for="jep-featured-instagram">{{ __('dashboard.jep_admin.featured.instagram_url') }}</label><input id="jep-featured-instagram" class="form-control" type="url" name="featured_indicator_instagram_url" value="{{ old('featured_indicator_instagram_url', $snapshot?->featured_indicator_instagram_url) }}"></div>
                        <div class="jep-featured-admin-field"><label for="jep-featured-x">{{ __('dashboard.jep_admin.featured.x_url') }}</label><input id="jep-featured-x" class="form-control" type="url" name="featured_indicator_x_url" value="{{ old('featured_indicator_x_url', $snapshot?->featured_indicator_x_url) }}"></div>
                        <div class="jep-featured-admin-field"><label for="jep-featured-read-more">{{ __('dashboard.jep_admin.featured.read_more_url') }}</label><input id="jep-featured-read-more" class="form-control" type="url" name="featured_indicator_read_more_url" value="{{ old('featured_indicator_read_more_url', $snapshot?->featured_indicator_read_more_url) }}"></div>
                    </div>
                    <button class="btn btn-primary jep-admin-form__submit" type="submit">{{ __('dashboard.jep_admin.featured.update') }}</button>
                </form>
            </section>
            @include('admin.organizations.jep.history', ['history' => $featuredHistory, 'kind' => 'featured'])
        @else
            <section class="jep-admin-section-card"><header class="jep-admin-section-card__header"><div><h2>Cifras de JEP</h2><p>Consulta la versión editorial vigente.</p></div></header></section>
        @endcan
    </main>

    @include('components.flash-toast', ['toasts' => [session('jep_metrics_success') ? ['type' => 'success', 'message' => session('jep_metrics_success')] : null, session('jep_metrics_info') ? ['type' => 'info', 'message' => session('jep_metrics_info')] : null]])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('jep-fetch-monthly-alert');
            const urlInput = document.getElementById('jep-monthly-alert-url');
            const excerpt = document.getElementById('jep-alert-excerpt');
            const status = document.getElementById('jep-fetch-monthly-alert-status');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const featuredImageInput = document.getElementById('jep-featured-image');
            const featuredPreviewImage = document.getElementById('jep-featured-preview-image');
            const featuredPreviewPlaceholder = document.getElementById('jep-featured-preview-placeholder');
            let featuredPreviewObjectUrl = null;

            if (featuredImageInput && featuredPreviewImage && featuredPreviewPlaceholder) {
                featuredImageInput.addEventListener('change', () => {
                    const [file] = featuredImageInput.files || [];
                    if (!file) return;

                    if (featuredPreviewObjectUrl) URL.revokeObjectURL(featuredPreviewObjectUrl);
                    featuredPreviewObjectUrl = URL.createObjectURL(file);
                    featuredPreviewImage.src = featuredPreviewObjectUrl;
                    featuredPreviewImage.alt = file.name;
                    featuredPreviewImage.classList.remove('d-none');
                    featuredPreviewPlaceholder.classList.add('d-none');
                });
            }

            if (!button || !urlInput || !excerpt || !status) return;
            button.addEventListener('click', async () => {
                status.textContent = 'Buscando publicación...';
                status.className = 'form-text text-muted';
                button.disabled = true;
                try {
                    const response = await fetch(button.dataset.endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken || '' }, body: JSON.stringify({ url: urlInput.value }) });
                    if (response.status === 419) throw new Error('La sesión expiró. Actualiza la página e inténtalo nuevamente.');
                    const payload = await response.json();
                    if (!response.ok || !payload.success) throw new Error(payload.message || 'No fue posible obtener el contenido de la publicación.');
                    excerpt.value = payload.text;
                    excerpt.dispatchEvent(new Event('input', { bubbles: true }));
                    status.textContent = 'Publicación recuperada. Revisa el texto antes de guardar.';
                    status.className = 'form-text text-success';
                } catch (error) { status.textContent = error.message; status.className = 'form-text text-danger'; }
                finally { button.disabled = false; }
            });
        });
    </script>
</x-layouts::admin>
