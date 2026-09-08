<section class="jep-section jep-indicators" aria-labelledby="jep-indicators-title">
    <header class="jep-section__header"><span></span><h2 id="jep-indicators-title">{{ __('dashboard.jep_page.indicators.title') }}</h2></header>

    <div class="jep-indicators-layout">
        <div class="jep-indicators-layout__left">
            <div class="jep-indicator-kpi-row">
                @foreach ([
                    ['icon' => 'bi-shield-check', 'value' => $jepSnapshot?->active_retired_officials ?? 0, 'label' => __('dashboard.jep_page.indicators.officials')],
                    ['icon' => 'bi-person-plus', 'value' => $jepSnapshot?->new_detentions ?? 0, 'label' => __('dashboard.jep_page.indicators.new_detentions')],
                    ['icon' => 'bi-geo-alt', 'value' => $jepSnapshot?->missing_location ?? 0, 'label' => __('dashboard.jep_page.indicators.missing_whereabouts')],
                ] as $indicator)
                    <article class="jep-indicator-card jep-indicator-card--value">
                        <div class="jep-indicator-card__top"><i class="bi {{ $indicator['icon'] }}" aria-hidden="true"></i><div class="jep-indicator-card__value-wrap"><strong>{{ number_format($indicator['value'], 0, ',', '.') }}</strong><span class="jep-indicator-card__accent" aria-hidden="true"></span></div></div>
                        <p>{{ $indicator['label'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="jep-indicator-bottom-row">
                <article class="jep-indicator-card jep-indicator-card--donut jep-indicator-card--custody">
                    <header class="jep-indicator-card__header"><i class="bi bi-heartbreak" aria-hidden="true"></i><p>{{ __('dashboard.jep_page.indicators.deaths_in_custody') }}</p></header>
                    @php($deathDistribution = $jepSnapshot?->deathCustodyDistribution ?? collect())
                    @php($deathDistributionValues = $deathDistribution->pluck('value')->values())
                    <div class="jep-donut-layout jep-donut-card__body">
                        <div class="jep-donut"><canvas id="jepDeathsCustodyChart" class="jep-donut__canvas" data-values='@json($deathDistributionValues)' data-colors='[\"#6f4bb8\", \"#9874d3\", \"#c8b5e8\"]' role="img" aria-label="{{ __('dashboard.jep_page.indicators.deaths_distribution_aria') }}"></canvas><span class="jep-donut__total"><strong>{{ number_format((int) $deathDistributionValues->sum(), 0, ',', '.') }}</strong><small>{{ __('dashboard.jep_page.indicators.total') }}</small></span></div>
                        <ul class="jep-donut-legend">
                            @foreach ($deathDistribution as $distribution)
                                <li style="--legend-color: {{ ['#6f4bb8', '#9874d3', '#c8b5e8'][$loop->index % 3] }}"><span>{{ __('dashboard.jep_page.indicators.death_categories.' . $distribution->category_key) }}</span><strong>{{ number_format($distribution->value, 0, ',', '.') }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                    <p class="jep-custody-period">{{ __('dashboard.jep_page.indicators.period_from') }} {{ $jepDeathsPeriod }}</p>
                </article>

                @php($vulnerableValues = collect($vulnerableGroups)->pluck('value')->values())
                @php($vulnerableColors = ['#F28A1A', '#F5AA3C', '#F7C967'])
                <article class="jep-indicator-card jep-indicator-card--donut jep-indicator-card--vulnerable">
                    <header class="jep-indicator-card__header"><i class="bi bi-people" aria-hidden="true"></i><p>{{ __('dashboard.jep_page.indicators.vulnerable_groups') }}</p></header>
                    <div class="jep-donut-layout jep-donut-card__body">
                        <div class="jep-donut"><canvas id="jepVulnerableGroupsChart" class="jep-donut__canvas" data-values='@json($vulnerableValues)' data-colors='@json($vulnerableColors)' role="img" aria-label="{{ __('dashboard.jep_page.indicators.vulnerable_groups') }}"></canvas><span class="jep-donut__total"><strong>{{ number_format((int) $vulnerableValues->sum(), 0, ',', '.') }}</strong><small>{{ __('dashboard.jep_page.indicators.total') }}</small></span></div>
                        <ul class="jep-donut-legend">
                            @foreach ($vulnerableGroups as $group)
                                <li style="--legend-color: {{ $vulnerableColors[$loop->index % count($vulnerableColors)] }}"><span>{{ __('dashboard.jep_page.indicators.vulnerable_categories.' . $group['group_key']) }}</span><strong>{{ number_format($group['value'], 0, ',', '.') }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            </div>
        </div>

        <div class="jep-indicators-layout__right">
        @php($maxDetentionCenter = max(1, (int) collect($detentionCenters)->max('value')))
        <article class="jep-indicator-card jep-indicator-card--centers">
            <header class="jep-indicator-card__header"><i class="bi bi-building" aria-hidden="true"></i><p>{{ __('dashboard.jep_page.centers.title') }}</p></header>
            <ol class="jep-centers-ranking">
                @foreach ($detentionCenters as $center)
                    <li><div class="jep-centers-ranking__row"><span><i class="bi bi-building" aria-hidden="true"></i>{{ $center['name'] }}</span><strong>{{ number_format($center['value'], 0, ',', '.') }}</strong></div><span class="jep-centers-ranking__track" aria-hidden="true"><span style="width: {{ min(100, ((float) $center['value'] / $maxDetentionCenter) * 100) }}%"></span></span></li>
                @endforeach
            </ol>
        </article>
        @include('dashboard.organizations.jep.methodology-detentions')
        </div>
    </div>
</section>
