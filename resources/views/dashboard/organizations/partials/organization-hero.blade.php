<header
    class="access-v2-header dashboard-v2-header organization-v2-hero {{ $heroClass ?? '' }}"
    style="--organization-accent: {{ $accent }}; --organization-accent-rgb: {{ $accentRgb }};"
>
    <div class="dashboard-v2-container access-v2-shell">
        <div class="access-v2-hero organization-v2-hero__content">
            <div class="access-v2-organization-logo organization-v2-hero__logo">
                @if (filled($logo))
                    <img src="{{ $logo }}" alt="{{ $title }}">
                @else
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                @endif
            </div>

            <div class="access-v2-hero-copy organization-v2-hero__text">
                <span class="access-v2-eyebrow organization-v2-hero__category">{{ $category }}</span>
                <h1>{{ $title }}</h1>
                <span class="access-v2-title-rule organization-v2-hero__accent" aria-hidden="true"></span>
                <p>{{ $description }}</p>
            </div>

            @include('dashboard.organizations.partials.mobile-update', [
                'lastSyncAt' => $lastSyncAt,
                'accent' => $accent,
            ])

            <div class="organization-v2-visual">
                @include($illustrationPartial)

                @include('dashboard.organizations.partials.update-card', [
                    'lastSyncAt' => $lastSyncAt,
                    'timeLabel' => $timeLabel,
                    'class' => $updateClass ?? '',
                ])
            </div>
        </div>
    </div>
</header>
