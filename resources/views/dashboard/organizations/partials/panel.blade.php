<div class="organization-page organization-page--{{ $theme }}">
    <header class="organization-page__hero">
        <div class="organization-page__container">
            <nav class="organization-page__nav">
                <a href="{{ route('dashboard.public') }}">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('dashboard.general_overview') }}
                </a>

                <div class="language-switcher language-switcher--light">
                    <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}">ES</a>
                    <span>|</span>
                    <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}">EN</a>
                </div>
            </nav>

            <div class="organization-page__identity">
                <span class="organization-page__logo">
                    <img src="{{ $organization['logo'] }}" alt="{{ $organization['name'] }}">
                </span>

                <div>
                    <p>{{ $sectionLabel }}</p>
                    <h1>{{ $organization['name'] }}</h1>

                    <a
                        href="https://x.com/{{ $organization['username'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ '@'.$organization['username'] }}
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="organization-page__container organization-page__main">
        @if ($showStats)
            <section class="organization-page__stats" aria-label="{{ __('dashboard.key_figures') }}">
                @foreach ($stats as $stat)
                    <article>
                        <i class="bi {{ $stat['icon'] }}"></i>
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                        <small class="{{ $stat['sentiment'] === 'positive' ? 'is-positive' : 'is-negative' }}">
                            {{ $stat['change'] }}
                        </small>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="organization-page__publications">
            <div class="organization-page__heading">
                <div>
                    <span></span>
                    <h2>{{ __('dashboard.latest_content') }}</h2>
                </div>

                <small>
                    @php($publicationCount = method_exists($posts, 'total') ? $posts->total() : $posts->count())
                    {{ trans_choice('dashboard.publication_count', $publicationCount, ['count' => $publicationCount]) }}
                </small>
            </div>

            @if ($searchable ?? false)
                <form
                    action="{{ route('organizations.acceso-justicia') }}"
                    method="GET"
                    class="organization-page__search"
                    role="search"
                >
                    <div>
                        <i class="bi bi-search"></i>
                        <input
                            type="search"
                            name="q"
                            value="{{ $search ?? '' }}"
                            placeholder="{{ __('dashboard.search_legal_alerts') }}"
                            aria-label="{{ __('dashboard.search_publications') }}"
                        >
                    </div>

                    <button type="submit">{{ __('dashboard.search_button') }}</button>

                    @if (filled($search ?? ''))
                        <a href="{{ route('organizations.acceso-justicia') }}">{{ __('dashboard.clear_search') }}</a>
                    @endif
                </form>
            @endif

            <div class="organization-page__grid">
                @forelse ($posts as $post)
                    <a
                        href="{{ $post['url'] ?? '#' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="organization-page-post"
                    >
                        <div class="organization-page-post__image">
                            @if ($post['image'] ?? null)
                                <img src="{{ $post['image'] }}" alt="" loading="lazy">
                            @else
                                <i class="bi bi-twitter-x"></i>
                            @endif
                        </div>

                        <div class="organization-page-post__body">
                            @if ($post['created_at'] ?? null)
                                <time>
                                    {{ \Carbon\Carbon::parse($post['created_at'])->locale(app()->getLocale())->diffForHumans() }}
                                </time>
                            @endif

                            <h3>{{ \Illuminate\Support\Str::limit($post['text'] ?? '', 210) }}</h3>

                            <div>
                                <span><i class="bi bi-heart"></i> {{ $post['likes'] ?? 0 }}</span>
                                <span><i class="bi bi-repeat"></i> {{ $post['retweets'] ?? 0 }}</span>
                                <strong>{{ __('dashboard.view_on_x') }} <i class="bi bi-arrow-right"></i></strong>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="organization-page__empty">
                        <i class="bi bi-inbox"></i>
                        <p>{{ __('dashboard.no_synced_publications') }}</p>
                    </div>
                @endforelse
            </div>

            @if (method_exists($posts, 'hasPages') && $posts->hasPages())
                <div class="organization-page__pagination">
                    {{ $posts->links('dashboard.partials.pagination') }}
                </div>
            @endif
        </section>
    </main>
</div>
