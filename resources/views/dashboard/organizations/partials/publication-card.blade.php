@php
    $isLegalAlert = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($post['text'] ?? ''), '#alertalegal');
    $publicationUrl = $isLegalAlert && filled($post['publication_id'] ?? null)
        ? route('analytics.content.redirect', ['publication' => $post['publication_id'], 'source' => 'organization'])
        : ($post['url'] ?? '#');
@endphp

<article class="access-justice-publication">
    <a
        href="{{ $publicationUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="access-justice-publication__link"
    >
        <div class="access-justice-publication__media">
            @if ($post['image'] ?? null)
                <img src="{{ $post['image'] }}" alt="" loading="lazy">
            @else
                <div class="access-justice-publication__placeholder">
                    <i class="bi bi-twitter-x" aria-hidden="true"></i>
                </div>
            @endif
        </div>

        <div class="access-justice-publication__body">
            <div class="access-justice-publication__meta">
                @if ($post['created_at'] ?? null)
                    <time datetime="{{ $post['created_at'] }}">
                        {{ \Carbon\Carbon::parse($post['created_at'])->locale(app()->getLocale())->diffForHumans() }}
                    </time>
                @endif

                <span class="access-justice-publication__source-badge">
                    {{ $isLegalAlert ? __('dashboard.legal_alert_badge') : __('dashboard.x_badge') }}
                </span>
            </div>

            <p>{{ \Illuminate\Support\Str::limit($post['text'] ?? '', 210) }}</p>

            <footer>
                @if ($post['created_at'] ?? null)
                    <time datetime="{{ $post['created_at'] }}">
                        {{ \Carbon\Carbon::parse($post['created_at'])->locale(app()->getLocale())->translatedFormat('d M Y') }}
                    </time>
                @endif
                <span aria-label="{{ __('dashboard.likes') }}">
                    <i class="bi bi-heart" aria-hidden="true"></i>
                    {{ $post['likes'] ?? 0 }}
                </span>
                <span aria-label="{{ __('dashboard.retweets') }}">
                    <i class="bi bi-repeat" aria-hidden="true"></i>
                    {{ $post['retweets'] ?? 0 }}
                </span>
                <strong>
                    {{ __('dashboard.view_on_x') }}
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </strong>
            </footer>
        </div>
    </a>
</article>
