<section class="jep-section jep-indicators" aria-labelledby="jep-indicators-title">
    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-indicators-title">{{ __('dashboard.jep_page.indicators.title') }}</h2>
    </header>

    <div class="jep-indicator-grid">
        <article class="jep-indicator-card jep-indicator-card--value">
            <span>A</span><i class="bi bi-shield-check" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.officials') }}</p><strong>74</strong>
        </article>
        <article class="jep-indicator-card jep-indicator-card--value">
            <span>B</span><i class="bi bi-person-plus" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.new_detentions') }}</p><strong>186</strong>
        </article>
        <article class="jep-indicator-card jep-indicator-card--value">
            <span>C</span><i class="bi bi-geo-alt" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.missing_whereabouts') }}</p><strong>12</strong>
        </article>
        <article class="jep-indicator-card jep-indicator-card--list">
            <span>D</span><i class="bi bi-building" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.main_centers') }}</p>
            <ol>
                @foreach ($detentionCenters as $center)
                    <li><span>{{ $center['name'] }}</span><strong>{{ $center['value'] }}</strong></li>
                @endforeach
            </ol>
        </article>
        <article class="jep-indicator-card jep-indicator-card--list">
            <span>E</span><i class="bi bi-heartbreak" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.deaths_in_custody') }}</p>
            <ul>
                @foreach ($custodyDeaths as $item)
                    <li><span><b>{{ $item['letter'] }}</b>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>
                @endforeach
            </ul>
        </article>
        <article class="jep-indicator-card jep-indicator-card--list">
            <span>F</span><i class="bi bi-people" aria-hidden="true"></i>
            <p>{{ __('dashboard.jep_page.indicators.vulnerable_groups') }}</p>
            <ul>
                @foreach ($vulnerableGroups as $item)
                    <li><span><b>{{ $item['letter'] }}</b>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>
                @endforeach
            </ul>
        </article>
        <article class="jep-indicator-card jep-indicator-card--alert">
            <span>G</span><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div>
                <p>{{ __('dashboard.jep_page.indicators.monthly_alert') }}</p>
                <blockquote>{{ __('dashboard.jep_page.indicators.alert_text') }}</blockquote>
                <a href="#">{{ __('dashboard.jep_page.indicators.view_alert') }} <i class="bi bi-arrow-right"></i></a>
            </div>
        </article>
    </div>
</section>
