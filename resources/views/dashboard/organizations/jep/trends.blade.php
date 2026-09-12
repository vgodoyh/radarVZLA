@php
    $featuredIndicatorTitle = $jepSnapshot?->featured_indicator_title ?: __('dashboard.featured_title');
    $featuredIndicatorText = $jepSnapshot?->featured_indicator_text ?: __('dashboard.featured_analysis_jep');
        $featuredIndicatorImage = filled($jepSnapshot?->featured_indicator_image_path)
            ? \Illuminate\Support\Facades\Storage::url($jepSnapshot->featured_indicator_image_path)
            : null;
    $featuredIndicatorReadMoreUrl = $jepSnapshot?->featured_indicator_read_more_url;
    $featuredIndicatorSnapshotId = $jepSnapshot?->id;
@endphp

<section class="jep-section jep-featured-indicator" aria-labelledby="jep-featured-indicator-title">
    <header class="jep-section__header">
        <span></span>
        <h2 id="jep-featured-indicator-title">{{ __('dashboard.featured_indicator') }}</h2>
    </header>

    <article class="jep-featured-indicator__card{{ $featuredIndicatorImage ? ' jep-featured-indicator__card--with-image' : '' }}">
        <div class="jep-featured-indicator__copy">
            <h3>{{ $featuredIndicatorTitle }}</h3>
            <p class="jep-featured-indicator__text">{{ $featuredIndicatorText }}</p>
            <div class="jep-featured-indicator__links">
                @if (filled($jepSnapshot?->featured_indicator_instagram_url))
                    <a href="{{ route('analytics.jep.featured.redirect', ['publication' => $featuredIndicatorSnapshotId, 'type' => 'featured_instagram', 'source' => 'organization']) }}" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-instagram" aria-hidden="true"></i>
                        {{ __('dashboard.jep_page.trends_actions.instagram') }}
                    </a>
                @endif
                @if (filled($jepSnapshot?->featured_indicator_x_url))
                    <a href="{{ route('analytics.jep.featured.redirect', ['publication' => $featuredIndicatorSnapshotId, 'type' => 'featured_x', 'source' => 'organization']) }}" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-twitter-x" aria-hidden="true"></i>
                        {{ __('dashboard.jep_page.trends_actions.x_thread') }}
                    </a>
                @endif
                @if (filled($featuredIndicatorReadMoreUrl))
                    <a href="{{ route('analytics.jep.featured.redirect', ['publication' => $featuredIndicatorSnapshotId, 'type' => 'featured_read_more', 'source' => 'organization']) }}" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-arrow-up-right-square" aria-hidden="true"></i>
                        {{ __('dashboard.jep_page.trends_actions.read_more') }}
                    </a>
                @endif
            </div>
        </div>

        @if ($featuredIndicatorImage)
            <figure class="jep-featured-indicator__media">
                <img src="{{ $featuredIndicatorImage }}" alt="{{ $featuredIndicatorTitle }}">
            </figure>
        @endif
    </article>
</section>
