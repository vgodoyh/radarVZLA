@php
    $lastSyncAt = filled($lastSync)
        ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
        : null;
@endphp

<header class="access-justice-detail__hero hero-section--light">
    <div class="access-justice-detail__container">
        <nav class="access-justice-detail__topbar">
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

        <div class="access-justice-detail__hero-grid">
            <div class="access-justice-detail__hero-content">

                <div class="jep-page__identity jep-page__identity--acceso">
                    <span class="jep-page__logo">
                        <img src="{{ asset('assets/img/organizations/acceso-justicia-x.png') }}" alt="{{ $organization['name'] }}">
                    </span>

                    <div>
                        <span class="jep-page__eyebrow">{{ $sectionLabel }}</span>
                        <h1>{{ $organization['name'] }}</h1>
                        <span class="jep-page__eyebrow">{{ __('dashboard.rule_of_law') }}</span>

                        <p class="jep-page__description">
                            {{ __('dashboard.acceso_institutional_description') }}
                        </p>

                        <div class="jep-page__links">
                            <a
                                href="https://x.com/{{ $organization['username'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-twitter-x" aria-hidden="true"></i>
                                {{ '@'.$organization['username'] }}
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            </a>

                            <a
                                href="https://accesoalajusticia.org/"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-globe2" aria-hidden="true"></i>
                                accesoalajusticia.org
                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="access-justice-detail__visual">
                <div class="access-justice-detail__map" aria-hidden="true">
                    <img src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}" alt="">
                </div>

                <div class="hero-update-card access-justice-detail__update-card">
                    <div class="hero-update-card__heading">
                        <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                        <span>{{ __('dashboard.data_updated') }}</span>
                    </div>

                    <div class="hero-update-card__date">
                        {{ $lastSyncAt ? $lastSyncAt->translatedFormat('d M Y') : __('dashboard.pending_sync') }}
                    </div>

                    <div class="hero-update-card__divider"></div>

                    @if ($lastSyncAt)
                        <div class="hero-update-card__time">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <span>{{ $lastSyncAt->format('H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
