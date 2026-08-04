<section class="jep-section jep-key-figures" aria-labelledby="jep-key-figures-title">
    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-key-figures-title">{{ __('dashboard.jep_page.key_figures') }}</h2>
    </header>

    <div class="jep-key-figures__layout">
        @php($mainFigure = $keyFigures[0])
        <article class="jep-key-figures__primary">
            <i class="bi {{ $mainFigure['icon'] }}" aria-hidden="true"></i>
            <p>{{ $mainFigure['label'] }}</p>
            <strong>{{ $mainFigure['value'] }}</strong>
            <span class="jep-change jep-change--{{ $mainFigure['tone'] }}">{{ $mainFigure['change'] }}</span>
            <small>{{ __('dashboard.previous_month_comparison') }}</small>
        </article>

        <div class="jep-key-figures__grid">
            @foreach (array_slice($keyFigures, 1) as $figure)
                <article class="jep-key-figures__item">
                    <i class="bi {{ $figure['icon'] }}" aria-hidden="true"></i>
                    <div>
                        <p>{{ $figure['label'] }}</p>
                        <strong>{{ $figure['value'] }}</strong>
                        <span class="jep-change jep-change--{{ $figure['tone'] }}">{{ $figure['change'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
