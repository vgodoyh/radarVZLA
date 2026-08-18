<section class="jep-section jep-indicators" aria-labelledby="jep-indicators-title">
    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-indicators-title">{{ __('dashboard.jep_page.indicators.title') }}</h2>
    </header>

    <div class="jep-indicator-grid jep-indicator-grid--main">
        <article class="jep-indicator-card jep-indicator-card--value">
            <div class="jep-indicator-card__top">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <div class="jep-indicator-card__value-wrap">
                    <strong>74</strong>
                    <span class="jep-indicator-card__accent" aria-hidden="true"></span>
                </div>
            </div>
            <p>{{ __('dashboard.jep_page.indicators.officials') }}</p>
        </article>
        <article class="jep-indicator-card jep-indicator-card--value">
            <div class="jep-indicator-card__top">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
                <div class="jep-indicator-card__value-wrap">
                    <strong>186</strong>
                    <span class="jep-indicator-card__accent" aria-hidden="true"></span>
                </div>
            </div>
            <p>{{ __('dashboard.jep_page.indicators.new_detentions') }}</p>
        </article>
        <article class="jep-indicator-card jep-indicator-card--value">
            <div class="jep-indicator-card__top">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                <div class="jep-indicator-card__value-wrap">
                    <strong>12</strong>
                    <span class="jep-indicator-card__accent" aria-hidden="true"></span>
                </div>
            </div>
            <p>{{ __('dashboard.jep_page.indicators.missing_whereabouts') }}</p>
        </article>
        <div class="jep-indicator-grid__aside">
            @php($maxDetentionCenter = max(1, (int) collect($detentionCenters)->max('value')))
            <article class="jep-indicator-card jep-indicator-card--centers">
                <header class="jep-indicator-card__header">
                    <i class="bi bi-building" aria-hidden="true"></i>
                    <p>{{ __('dashboard.jep_page.indicators.main_centers') }}</p>
                </header>
                <ol class="jep-centers-ranking">
                    @foreach ($detentionCenters as $center)
                        <li>
                            <div class="jep-centers-ranking__row">
                                <span><i class="bi bi-building" aria-hidden="true"></i>{{ $center['name'] }}</span>
                                <strong>{{ $center['value'] }} <small>{{ __('dashboard.jep_page.centers.prisoners') }}</small></strong>
                            </div>
                            <span class="jep-centers-ranking__track" aria-hidden="true">
                                <span style="width: {{ min(100, ((float) $center['value'] / $maxDetentionCenter) * 100) }}%"></span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            </article>

            @include('dashboard.organizations.jep.methodology-detentions')
        </div>
        <div class="jep-indicator-grid__support">
            @php($custodyDeathsTotal = (int) collect($custodyDeaths)->sum('value'))
            <article class="jep-indicator-card jep-indicator-card--donut jep-indicator-card--custody">
                <header class="jep-indicator-card__header">
                    <i class="bi bi-heartbreak" aria-hidden="true"></i>
                    <p>{{ __('dashboard.jep_page.indicators.deaths_in_custody') }}</p>
                </header>
                <div class="jep-donut-layout">
                    <div class="jep-donut">
                        <canvas class="jep-donut__canvas" data-values='@json(collect($custodyDeaths)->pluck('value')->values())' data-colors='["#6f4bb8","#9874d3","#c8b5e8"]' role="img" aria-label="{{ __('dashboard.jep_page.indicators.deaths_in_custody') }}"></canvas>
                        <span class="jep-donut__total"><strong>{{ $custodyDeathsTotal }}</strong><small>{{ __('dashboard.jep_page.indicators.total') }}</small></span>
                    </div>
                    <ul class="jep-donut-legend">
                        @foreach ($custodyDeaths as $item)
                            <li style="--legend-color: {{ ['#6f4bb8', '#9874d3', '#c8b5e8'][$loop->index % 3] }}"><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            </article>
            @php($vulnerableGroupsTotal = (int) collect($vulnerableGroups)->sum('value'))
            <article class="jep-indicator-card jep-indicator-card--donut jep-indicator-card--vulnerable">
                <header class="jep-indicator-card__header">
                    <i class="bi bi-people" aria-hidden="true"></i>
                    <p>{{ __('dashboard.jep_page.indicators.vulnerable_groups') }}</p>
                </header>
                <div class="jep-donut-layout">
                    <div class="jep-donut">
                        <canvas class="jep-donut__canvas" data-values='@json(collect($vulnerableGroups)->pluck('value')->values())' data-colors='["#e58a2b","#f2ad4f","#f7cc78"]' role="img" aria-label="{{ __('dashboard.jep_page.indicators.vulnerable_groups') }}"></canvas>
                        <span class="jep-donut__total"><strong>{{ $vulnerableGroupsTotal }}</strong><small>{{ __('dashboard.jep_page.indicators.total') }}</small></span>
                    </div>
                    <ul class="jep-donut-legend">
                        @foreach ($vulnerableGroups as $item)
                            <li style="--legend-color: {{ ['#e58a2b', '#f2ad4f', '#f7cc78'][$loop->index % 3] }}"><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            </article>
        </div>
    </div>

</section>
