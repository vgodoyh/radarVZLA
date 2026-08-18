<section class="jep-hero-metrics" aria-label="{{ __('dashboard.jep_page.key_figures') }}">
    <div class="jep-page__container jep-figures-layout">
        @php($mainFigure = $keyFigures[0])

        <article class="jep-figure-primary">
            <span class="jep-figure-primary__icon">
                <i class="bi {{ $mainFigure['icon'] }}" aria-hidden="true"></i>
            </span>
            <p>{{ $mainFigure['label'] }}</p>
            <strong>{{ $mainFigure['value'] }}</strong>
            <span class="jep-change jep-change--{{ $mainFigure['tone'] }}">{{ $mainFigure['change'] }}</span>
            <small>{{ __('dashboard.previous_month_comparison') }}</small>
        </article>

        <div class="jep-figures-secondary">
            @foreach (array_slice($keyFigures, 1) as $figure)
                <article class="jep-figure-secondary">
                    <span class="jep-figure-secondary__icon">
                        <i class="bi {{ $figure['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <p>{{ $figure['label'] }}</p>
                    <strong>{{ $figure['value'] }}</strong>
                    <small class="jep-change jep-change--{{ $figure['tone'] }}">{{ $figure['change'] }}</small>
                </article>
            @endforeach
        </div>
    </div>
</section>
