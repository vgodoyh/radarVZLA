<aside class="access-justice-detail__methodology">
    <span class="hero-kpi-card__icon hero-kpi-card__icon--orange access-justice-detail__methodology-icon" aria-hidden="true">
        <i class="bi bi-info-circle"></i>
    </span>

    <div>
        <h2>{{ __('dashboard.methodology_note_title') }}</h2>
        <p>{{ __('dashboard.acceso_methodology_note') }}</p>

        @if (filled($organization['website_url'] ?? null))
            <a href="{{ $organization['website_url'] }}" target="_blank" rel="noopener noreferrer">
                {{ __('dashboard.about_acceso_justicia') }}
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
            </a>
        @endif
    </div>
</aside>
