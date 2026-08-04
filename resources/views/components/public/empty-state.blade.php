@props(['message' => null])

<div class="feed-empty" role="status">
    <i class="bi bi-info-circle"></i>
    <p>{{ $message ?? __('dashboard.feed_unavailable') }}</p>
</div>
