@if ($history->count() > 0)
    @php
        $historyTitle = [
            'main' => 'Cifras principales', 'featured' => 'Indicador destacado', 'death' => 'Distribución de fallecidos',
            'methodology' => 'Nota metodológica', 'alert' => 'Alerta del mes', 'indicators' => 'Indicadores',
            'groups' => 'Grupos vulnerables', 'centers' => 'Centros de detención',
        ][$kind] ?? 'Módulo';
        $date = fn ($value) => $value?->format('d/m/Y H:i') ?? 'Vigente';
        $excerpt = fn ($value) => \Illuminate\Support\Str::limit((string) $value, 120);
    @endphp
    <section class="jep-admin-history-card">
        <header class="jep-admin-history-card__header"><div><h3>Historial de {{ $historyTitle }}</h3><p>Versiones en las que este bloque fue modificado.</p></div></header>
        <div class="jep-admin-history-card__table-wrap">
            <table class="table mb-0 jep-admin-history-table jep-admin-history-table--{{ $kind }}">
                <thead>
                    @if ($kind === 'main')
                        <tr><th>Actualizado hasta</th><th>Presos políticos</th><th>Mujeres</th><th>Enfermos graves</th><th>Extranjeros/doble</th><th>Excarcelaciones</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'featured')
                        <tr><th>Título</th><th>Texto</th><th>Imagen</th><th>Instagram</th><th>X</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'death')
                        <tr><th>Arresto domiciliario</th><th>Centros de reclusión</th><th>Hospitales</th><th>Total</th><th>Período</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'methodology')
                        <tr><th>Texto</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'alert')
                        <tr><th>Título</th><th>Resumen</th><th>URL X</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'indicators')
                        <tr><th>Funcionarios</th><th>Nuevas detenciones</th><th>Sin paradero</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @elseif ($kind === 'groups')
                        <tr><th>Sindicalistas</th><th>Organizaciones políticas</th><th>Sociedad civil</th><th>Total</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @else
                        <tr><th>Centros</th><th>Vigente desde</th><th>Vigente hasta</th><th>Modificado por</th></tr>
                    @endif
                </thead>
                <tbody>
                    @foreach ($history as $version)
                        @php($groupsByKey = $version->vulnerableGroups->keyBy('group_key'))
                        @php($deathByKey = $version->deathCustodyDistribution->keyBy('category_key'))
                        <tr>
                            @if ($kind === 'main')
                                <td>{{ $version->data_date?->format('d/m/Y') ?? 'Sin fecha' }}</td><td>{{ number_format($version->total_political_prisoners, 0, ',', '.') }}</td><td>{{ $version->women }}</td><td>{{ $version->seriously_ill }}</td><td>{{ $version->foreign_or_dual_nationality }}</td><td>{{ $version->releases }}</td>
                            @elseif ($kind === 'featured')
                                <td>{{ $version->featured_indicator_title }}</td><td>{{ $excerpt($version->featured_indicator_text) }}</td><td>{{ filled($version->featured_indicator_image_path) ? 'Sí' : 'No' }}</td><td>{{ filled($version->featured_indicator_instagram_url) ? 'Sí' : 'No' }}</td><td>{{ filled($version->featured_indicator_x_url) ? 'Sí' : 'No' }}</td>
                            @elseif ($kind === 'death')
                                <td>{{ $deathByKey->get('home_arrest')?->value ?? 0 }}</td><td>{{ $deathByKey->get('detention_centers')?->value ?? 0 }}</td><td>{{ $deathByKey->get('hospitals')?->value ?? 0 }}</td><td>{{ $version->deathCustodyDistribution->sum('value') }}</td><td>@if ($version->deaths_period_start_day && $version->deaths_period_end_day){{ sprintf('%02d/%02d/%04d', $version->deaths_period_start_day, $version->deaths_period_start_month, $version->deaths_period_start_year) }} – {{ sprintf('%02d/%02d/%04d', $version->deaths_period_end_day, $version->deaths_period_end_month, $version->deaths_period_end_year) }}@else{{ $version->deaths_period_start_month }}/{{ $version->deaths_period_start_year }} – {{ $version->deaths_period_end_month }}/{{ $version->deaths_period_end_year }}@endif</td>
                            @elseif ($kind === 'methodology')
                                <td>{{ $excerpt($version->detentions_methodology_note) }}</td>
                            @elseif ($kind === 'alert')
                                <td>{{ $version->monthly_alert_title }}</td><td><div class="jep-admin-history-excerpt">{{ $version->monthly_alert_excerpt }}</div></td><td>@if (filled($version->monthly_alert_x_url))<a class="jep-admin-history-url" href="{{ $version->monthly_alert_x_url }}" target="_blank" rel="noopener noreferrer">Ver publicación</a>@else<span>Sin URL</span>@endif</td>
                            @elseif ($kind === 'indicators')
                                <td>{{ $version->active_retired_officials }}</td><td>{{ $version->new_detentions }}</td><td>{{ $version->missing_location }}</td>
                            @elseif ($kind === 'groups')
                                <td>{{ $groupsByKey->get('sindicalistas')?->value ?? 0 }}</td><td>{{ $groupsByKey->get('organizaciones_politicas')?->value ?? 0 }}</td><td>{{ $groupsByKey->get('sociedad_civil')?->value ?? 0 }}</td><td>{{ $version->vulnerableGroups->sum('value') }}</td>
                            @else
                                <td><div class="jep-admin-history-centers">@foreach ($version->detentionCenters as $center)<span>{{ $center->name }} <strong>{{ $center->value }}</strong></span>@endforeach</div></td>
                            @endif
                            <td>{{ $date($version->valid_from) }}</td><td>{{ $date($version->valid_until) }}</td><td>{{ $version->user?->name ?? 'Sistema' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($history->hasPages())
            <div class="mt-3">{{ $history->withQueryString()->links() }}</div>
        @endif
    </section>
@endif
