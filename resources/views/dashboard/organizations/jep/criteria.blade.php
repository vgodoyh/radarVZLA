<section class="jep-section jep-criteria" aria-labelledby="jep-criteria-title">
    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-criteria-title">{{ __('dashboard.jep_page.criteria.title') }}</h2>
    </header>
    <p class="jep-criteria__intro">{{ __('dashboard.jep_page.criteria.intro') }}</p>
    <div class="jep-criteria__grid">
        @foreach ($criteria as $criterion)
            <article>
                <span>{{ $criterion['number'] }}</span>
                <p>{{ $criterion['text'] }}</p>
            </article>
        @endforeach
    </div>
    <div class="jep-criteria__note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <p>{{ __('dashboard.jep_page.criteria.note') }}</p>
    </div>
</section>
