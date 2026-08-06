<style>
    .fake-news-magazine-deep .fake-news-featured-analysis {
        grid-template-columns: 280px minmax(0, 1fr);
        min-height: 0;
        gap: 22px;
        padding: 14px;
    }

    .fake-news-magazine-deep .fake-news-featured-analysis__media {
        width: 280px;
        height: 220px;
        min-height: 0;
        border-radius: 16px;
    }

    .fake-news-magazine-deep .fake-news-featured-analysis__body {
        justify-content: flex-start;
        padding: 8px 16px 8px 0;
    }

    .fake-news-magazine-deep .fake-news-featured-analysis h4,
    .fake-news-magazine-deep .fake-news-secondary-analysis h4,
    .fake-news-magazine-deep .fake-news-featured-analysis__body > p,
    .fake-news-magazine-deep .fake-news-secondary-analysis p {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
    }

    .fake-news-magazine-deep .fake-news-featured-analysis h4,
    .fake-news-magazine-deep .fake-news-secondary-analysis h4 {
        -webkit-line-clamp: 2;
    }

    .fake-news-magazine-deep .fake-news-featured-analysis__body > p,
    .fake-news-magazine-deep .fake-news-secondary-analysis p {
        -webkit-line-clamp: 3;
    }

    .fake-news-magazine-deep .fake-news-secondary-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .fake-news-magazine-deep .fake-news-secondary-analysis {
        padding: 10px;
    }

    .fake-news-magazine-deep .fake-news-secondary-analysis__media {
        height: 140px;
        border-radius: 12px;
    }

    .fake-news-magazine-deep .fake-news-secondary-analysis > div {
        min-height: 190px;
        padding: 13px 3px 3px;
    }

    @media (max-width: 991.98px) {
        .fake-news-magazine-deep .fake-news-secondary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .fake-news-magazine-deep .fake-news-featured-analysis {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .fake-news-magazine-deep .fake-news-featured-analysis__media {
            width: 100%;
            height: 220px;
        }

        .fake-news-magazine-deep .fake-news-featured-analysis__body {
            padding: 18px 4px 4px;
        }

        .fake-news-magazine-deep .fake-news-secondary-grid {
            grid-template-columns: 1fr;
        }

        .fake-news-magazine-deep .fake-news-secondary-analysis {
            display: block;
        }

        .fake-news-magazine-deep .fake-news-secondary-analysis__media {
            height: 140px;
        }

        .fake-news-magazine-deep .fake-news-secondary-analysis > div {
            min-height: 0;
        }
    }
</style>

<section class="fake-news-magazine-panel fake-news-magazine-deep">
    <header class="fake-news-magazine-heading">
        <h3><span aria-hidden="true"></span>{{ __('dashboard.en_profundidad') }}</h3>
        @if (filled($organizationWebsite))
            <a href="{{ $organizationWebsite }}" target="_blank" rel="noopener noreferrer">
                {{ __('dashboard.fake_news_page.view_all') }}
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
        @endif
    </header>

    @if ($featuredDeepPost)
        @php($featuredDate = $localizedDate(data_get($featuredDeepPost, 'fecha')))
        <article class="fake-news-featured-analysis">
            <a href="{{ data_get($featuredDeepPost, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="fake-news-featured-analysis__media">
                @if (filled(data_get($featuredDeepPost, 'imagen')))
                    <img src="{{ data_get($featuredDeepPost, 'imagen') }}" alt="" loading="lazy">
                @else
                    <span><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
                @endif
            </a>

            <div class="fake-news-featured-analysis__body">
                <div class="fake-news-featured-analysis__meta">
                    <span>{{ __('dashboard.fake_news_page.featured_analysis') }}</span>
                    @if ($featuredDate)
                        <time datetime="{{ data_get($featuredDeepPost, 'fecha') }}">{{ $featuredDate }}</time>
                    @endif
                </div>
                <h4>{{ data_get($featuredDeepPost, 'titulo', __('dashboard.fake_news_page.untitled_publication')) }}</h4>
                @if (filled(data_get($featuredDeepPost, 'contenido')))
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags(data_get($featuredDeepPost, 'contenido')), 260) }}</p>
                @endif
                <a href="{{ data_get($featuredDeepPost, 'url', '#') }}" target="_blank" rel="noopener noreferrer">
                    {{ __('dashboard.fake_news_page.read_full_analysis') }}
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </article>

        @if ($secondaryDeepPosts->isNotEmpty())
            <div class="fake-news-secondary-grid">
                @foreach ($secondaryDeepPosts as $post)
                    @php($postDate = $localizedDate(data_get($post, 'fecha')))
                    <article class="fake-news-secondary-analysis">
                        <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer" class="fake-news-secondary-analysis__media">
                            @if (filled(data_get($post, 'imagen')))
                                <img src="{{ data_get($post, 'imagen') }}" alt="" loading="lazy">
                            @else
                                <span><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                            @endif
                        </a>
                        <div>
                            @if ($postDate)
                                <time datetime="{{ data_get($post, 'fecha') }}">{{ $postDate }}</time>
                            @endif
                            <h4>{{ data_get($post, 'titulo', __('dashboard.fake_news_page.untitled_publication')) }}</h4>
                            @if (filled(data_get($post, 'contenido')))
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags(data_get($post, 'contenido')), 90) }}</p>
                            @endif
                            <a href="{{ data_get($post, 'url', '#') }}" target="_blank" rel="noopener noreferrer">
                                {{ __('dashboard.fake_news_page.read_analysis') }}
                                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @else
        <div class="fake-news-magazine-empty">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <p>{{ __('dashboard.fake_news_page.no_deep_publications') }}</p>
        </div>
    @endif
</section>
