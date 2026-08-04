<section class="jep-section jep-trends" aria-labelledby="jep-trends-title">
    <header class="jep-section__header"><span></span><h2 id="jep-trends-title">{{ __('dashboard.jep_page.trends.title') }}</h2></header>
    <div class="jep-trends__tabs" role="tablist" aria-label="{{ __('dashboard.jep_page.trends.title') }}">
        <button type="button" class="active" role="tab" aria-selected="true">{{ __('dashboard.jep_page.trends.political_prisoners') }}</button>
        <button type="button" role="tab" aria-selected="false">{{ __('dashboard.jep_page.trends.releases') }}</button>
        <button type="button" role="tab" aria-selected="false">{{ __('dashboard.jep_page.trends.new_detentions') }}</button>
    </div>
    <div class="jep-trends__chart">
        <canvas
            id="jepTrendsChart"
            data-labels='@json(__('dashboard.jep_page.trends.months'))'
            data-values='[1350,1480,1570,1650,1710,1770,1810,1875]'
            aria-label="{{ __('dashboard.jep_page.trends.chart_label') }}"
        ></canvas>
    </div>
    <p class="jep-trends__note">{{ __('dashboard.jep_page.trends.note') }}</p>
</section>
