@php
    $lastSyncAt = filled($lastSync)
        ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
        : null;
@endphp

<header class="access-v2-header dashboard-v2-header">
    <div class="dashboard-v2-container access-v2-shell">
        <div class="dashboard-v2-navbar access-v2-navbar">
            <a href="{{ route('dashboard.public') }}" class="dashboard-v2-brand" aria-label="{{ __('dashboard.site_name') }}">
                <img
                    src="{{ asset('assets/img/pulso-venezuela-color.png') }}"
                    alt="{{ __('dashboard.site_name') }}"
                    class="dashboard-v2-brand__isotype"
                >
            </a>

            <nav class="dashboard-v2-navigation access-v2-navigation" aria-label="{{ __('dashboard.dashboard_v2.main_navigation') }}">
                <a href="{{ route('dashboard.public') }}">Inicio</a>
                <a href="{{ route('organizations.jep') }}">JEP</a>
                <a href="{{ route('organizations.acceso-justicia') }}" class="active">Acceso a la Justicia</a>
                <a href="{{ route('organizations.fake-news') }}">Fake News</a>
                <a href="{{ route('organizations.universidades') }}">OBU</a>
            </nav>

            <div class="dashboard-v2-language" aria-label="{{ __('dashboard.language') }}">
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}" lang="es">ES</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}" lang="en">EN</a>
            </div>
        </div>

        <div class="access-v2-hero">
            <div class="access-v2-organization-logo">
                <img src="{{ asset('assets/img/organizations/acceso-justicia-x.png') }}" alt="{{ $organization['name'] }}">
            </div>

            <div class="access-v2-hero-copy">
                <span class="access-v2-eyebrow">
                    {{ app()->isLocale('en') ? 'Rule of law and justice' : 'Estado de Derecho y Justicia' }}
                </span>
                <h1>{{ $organization['name'] }}</h1>
                <span class="access-v2-title-rule" aria-hidden="true"></span>
                <p>{{ __('dashboard.acceso_institutional_description') }}</p>
                <a href="https://accesoalajusticia.org/" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-globe2" aria-hidden="true"></i>
                    accesoalajusticia.org
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>

            @include('dashboard.organizations.partials.mobile-update', [
                'lastSyncAt' => $lastSyncAt,
                'accent' => '#ff6500',
            ])

            <div class="access-v2-justice-art" aria-hidden="true">
                <span class="access-v2-orbit access-v2-orbit--one"></span>
                <span class="access-v2-orbit access-v2-orbit--two"></span>
                <img src="{{ asset('assets/img/mapa-venezuela-radar.svg') }}" alt="">
                <i class="bi bi-bank2"></i>
                <span class="access-v2-art-dot access-v2-art-dot--one"></span>
                <span class="access-v2-art-dot access-v2-art-dot--two"></span>
            </div>

            <aside class="access-v2-update">
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
                            <small>{{ app()->isLocale('en') ? 'Venezuela time (GMT-4)' : 'Hora de Venezuela (GMT-4)' }}</small>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</header>
