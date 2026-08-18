<section class="jep-section jep-criteria" aria-labelledby="jep-criteria-title">
    @php($criterionIcons = ['bi-person', 'bi-megaphone', 'bi-people', 'bi-bank'])
    <header class="jep-criteria__header">
        <span><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
        <h2 id="jep-criteria-title">{{ __('dashboard.jep_page.criteria.title') }}</h2>
    </header>
    <p class="jep-criteria__intro">{{ __('dashboard.jep_page.criteria.intro') }}</p>
    <div class="jep-criteria__grid">
        @foreach ($criteria as $index => $criterion)
            <article>
                <i class="bi {{ $criterionIcons[$index] }}" aria-hidden="true"></i>
                <strong>{{ $criterion['number'] }}</strong>
                <span aria-hidden="true"></span>
                <p>{{ $criterion['text'] }}</p>
            </article>
        @endforeach
    </div>
    <div class="jep-criteria__note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <p>{{ __('dashboard.jep_page.criteria.note') }}</p>
    </div>
</section>
