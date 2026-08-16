<aside class="access-v2-update organization-v2-update {{ $class ?? '' }}">
    <div class="access-v2-update__heading">
        <i class="bi bi-calendar2-check" aria-hidden="true"></i>
        <span>{{ __('dashboard.data_updated') }}</span>
    </div>
    <span class="access-v2-update__divider"></span>
    <strong>{{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}</strong>
    <span class="access-v2-update__divider"></span>
    @if ($lastSyncAt)
        <div class="access-v2-update__time">
            <i class="bi bi-clock" aria-hidden="true"></i>
            <div>
                <b>{{ $lastSyncAt->format('H:i') }}</b>
                <small>{{ $timeLabel }}</small>
            </div>
        </div>
    @endif
</aside>
