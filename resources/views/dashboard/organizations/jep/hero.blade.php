@php
    $lastSyncAt = filled($lastSync ?? null)
        ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
        : null;
@endphp

<header class="jep-page__hero hero-section--light">
    <div class="jep-page__container">
        <nav class="jep-page__topbar">
            <a href="{{ route('dashboard.public') }}" class="hero-isotype" aria-label="{{ __('dashboard.site_name') }}">
                <img src="{{ asset('assets/img/isotipo-pulso.png') }}" alt="{{ __('dashboard.site_name') }}">
            </a>

            <div class="language-switcher language-switcher--light" aria-label="{{ __('dashboard.language') }}">
                <i class="bi bi-globe2" aria-hidden="true"></i>
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}">ES</a>
                <span class="language-switcher__separator">|</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}">EN</a>
            </div>
        </nav>

        <div class="jep-page__hero-grid">
            <div class="jep-page__hero-content">

                <div class="jep-page__identity">
                    <span class="jep-page__logo">
                        <img src="{{ $organization['logo'] }}" alt="{{ $organization['name'] }}">
                    </span>
                    <div>
                        <span class="jep-page__eyebrow">{{ __('dashboard.jep_page.badge') }}</span>
                        <h1>{{ $organization['name'] }}</h1>

                        <p class="jep-page__description">{{ __('dashboard.jep_page.description') }}</p>

                        <div class="jep-page__links">
                            <a href="https://x.com/{{ $organization['username'] }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-twitter-x" aria-hidden="true"></i>
                                {{ '@'.$organization['username'] }}
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            </a>

                            @if (filled($organization['website_url'] ?? null))
                                <a href="{{ $organization['website_url'] }}" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-globe2" aria-hidden="true"></i>
                                    {{ __('dashboard.visit_website') }}
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @include('dashboard.organizations.partials.mobile-update', [
                    'lastSyncAt' => $lastSyncAt,
                    'accent' => '#1769f6',
                ])
            </div>

            <div class="jep-page__visual">
                <div class="jep-page__map" aria-hidden="true">
                    <img src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}" alt="">
                </div>

                <div class="hero-update-card jep-page__update-card">
                    <div class="hero-update-card__heading">
                        <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                        <span>{{ __('dashboard.data_updated') }}</span>
                    </div>
                    <div class="hero-update-card__date {{ $lastSyncAt ? 'hero-update-card__date--synced' : '' }}">
                        {{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}
                    </div>
                    @if ($lastSyncAt)
                        <div class="hero-update-card__divider"></div>
                        <div class="hero-update-card__time">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <span>{{ $lastSyncAt->translatedFormat('h:i a') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
