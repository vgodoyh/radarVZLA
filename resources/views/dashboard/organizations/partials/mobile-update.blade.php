<div
    class="organization-mobile-update"
    style="--organization-mobile-accent: {{ $accent }};"
    aria-label="{{ __('dashboard.data_updated') }}"
>
    <div class="organization-mobile-update__date">
        <i class="bi bi-calendar2-check" aria-hidden="true"></i>
        <div>
            <span>{{ __('dashboard.data_updated') }}</span>
            <strong>
                {{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}
            </strong>
        </div>
    </div>

    <span class="organization-mobile-update__divider" aria-hidden="true"></span>

    <div class="organization-mobile-update__time">
        <i class="bi bi-clock" aria-hidden="true"></i>
        <div>
            <strong>{{ $lastSyncAt ? $lastSyncAt->format('H:i') : '—' }}</strong>
            <small>{{ app()->isLocale('en') ? 'Venezuela time (GMT-4)' : 'Hora de Venezuela (GMT-4)' }}</small>
        </div>
    </div>
</div>
