<section class="fake-news-magazine-panel fake-news-magazine-noti">
    <header class="fake-news-magazine-heading fake-news-magazine-heading--noti">
        <h3><span aria-hidden="true"></span>{{ __('dashboard.noti_fake') }}</h3>
        @if (filled($organizationWebsite))
            <a href="{{ $organizationWebsite }}" target="_blank" rel="noopener noreferrer">
                {{ __('dashboard.fake_news_page.view_all') }}
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
        @endif
    </header>

    <div class="fake-news-noti-list">
        @forelse ($magazineNotiFakePosts as $post)
            @php($postDate = $localizedDate(data_get($post, 'fecha')))
            <article class="fake-news-noti-card">
                <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="fake-news-noti-card__media">
                    @if (filled(data_get($post, 'imagen')))
                        <img src="{{ data_get($post, 'imagen') }}" alt="" loading="lazy">
                    @else
                        <span><i class="bi bi-newspaper" aria-hidden="true"></i></span>
                    @endif
                </a>
                <div class="fake-news-noti-card__body">
                    <div class="fake-news-noti-card__meta">
                        <span>{{ __('dashboard.noti_fake') }}</span>
                        @if ($postDate)
                            <time datetime="{{ data_get($post, 'fecha') }}">{{ $postDate }}</time>
                        @endif
                    </div>
                    <h4>{{ data_get($post, 'titulo', __('dashboard.fake_news_page.untitled_publication')) }}</h4>
                    @if (filled(data_get($post, 'contenido')))
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags(data_get($post, 'contenido')), 105) }}</p>
                    @endif
                    <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer">
                        {{ __('dashboard.fake_news_page.read_verification') }}
                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </a>
                </div>
            </article>
        @empty
            <div class="fake-news-magazine-empty">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <p>{{ __('dashboard.fake_news_page.no_noti_fake_publications') }}</p>
            </div>
        @endforelse
    </div>
</section>
