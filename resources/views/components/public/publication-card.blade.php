@props(['post'])

<a href="{{ $post['url'] }}" target="_blank" rel="noopener noreferrer" class="feed-post">
    <div class="feed-post__image">
        @if ($post['image'] ?? null)
            <img src="{{ $post['image'] }}" alt="" loading="lazy">
        @else
            <div class="feed-post__placeholder"><i class="bi bi-twitter-x"></i></div>
        @endif
    </div>
    <div class="feed-post__content">
        <p class="feed-post__title">{{ \Illuminate\Support\Str::limit($post['text'] ?? '', 90) }}</p>
        @if ($post['created_at'] ?? null)
            <time class="feed-post__time">{{ \Illuminate\Support\Carbon::parse($post['created_at'])->diffForHumans() }}</time>
        @endif
    </div>
</a>
