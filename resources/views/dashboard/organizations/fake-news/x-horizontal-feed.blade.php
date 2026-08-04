<section class="fake-news-x-strip">
    <header class="fake-news-magazine-heading fake-news-magazine-heading--x">
        <h3><i class="bi bi-twitter-x" aria-hidden="true"></i>{{ __('dashboard.fake_news_page.publications_on_x') }}</h3>
        @if ($xProfileUrl)
            <a href="{{ $xProfileUrl }}" target="_blank" rel="noopener noreferrer">
                {{ __('dashboard.view_on_x') }}
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
        @endif
    </header>

    <div class="fake-news-x-strip__track">
        @forelse ($magazineXPosts as $post)
            @php($xPostDate = $relativeDate(data_get($post, 'created_at')))
            <article class="fake-news-social-card">
                <header>
                    <span class="fake-news-social-card__avatar">
                        @if (filled($organizationLogo))
                            <img src="{{ $organizationLogo }}" alt="">
                        @else
                            <i class="bi bi-twitter-x" aria-hidden="true"></i>
                        @endif
                    </span>
                    <div>
                        <strong>{{ $organizationName }}</strong>
                        @if (filled($organizationUsername))
                            <span>{{ '@'.$organizationUsername }}</span>
                        @endif
                    </div>
                    <i class="bi bi-twitter-x" aria-hidden="true"></i>
                </header>

                <p>{{ \Illuminate\Support\Str::limit(data_get($post, 'text', ''), 150) }}</p>

                @if (filled(data_get($post, 'image')))
                    <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="fake-news-social-card__image">
                        <img src="{{ data_get($post, 'image') }}" alt="" loading="lazy">
                    </a>
                @endif

                <footer>
                    <div>
                        @if ($xPostDate)
                            <time datetime="{{ data_get($post, 'created_at') }}">{{ $xPostDate }}</time>
                        @endif
                        <span><i class="bi bi-heart" aria-hidden="true"></i>{{ data_get($post, 'likes', 0) }}</span>
                        <span><i class="bi bi-repeat" aria-hidden="true"></i>{{ data_get($post, 'retweets', 0) }}</span>
                    </div>
                    <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('dashboard.view_on_x') }}">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    </a>
                </footer>
            </article>
        @empty
            <div class="fake-news-magazine-empty fake-news-magazine-empty--x">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <p>{{ __('dashboard.fake_news_page.no_x_publications') }}</p>
            </div>
        @endforelse
    </div>
</section>
