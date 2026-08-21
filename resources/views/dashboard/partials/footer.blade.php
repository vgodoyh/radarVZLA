<footer class="site-footer {{ $footerClass ?? '' }}">
    <div class="site-footer__container {{ $containerClass ?? '' }}">
        <div class="site-footer__top">
            <div class="site-footer__brand site-footer__column">
                <a href="{{ route('dashboard.public') }}"
                    class="site-footer__logo"
                    aria-label="{{ __('dashboard.site_name') }}">
                    <img
                        src="{{ asset('assets/img/pulso-venezuela-color.png') }}"
                        alt="{{ __('dashboard.site_name') }}">
                </a>
                <span class="site-footer__brand-divider" aria-hidden="true"></span>
                <p>
                    {{ app()->isLocale('en')
                        ? 'Data and monitoring on human rights, justice, disinformation, and universities in Venezuela.'
                        : 'Datos y seguimiento sobre derechos humanos, justicia, desinformación y universidades en Venezuela.' }}
                </p>
            </div>

            <div class="site-footer__col site-footer__column">
                <p class="site-footer__col-title">{{ __('dashboard.name_organizations') }}</p>

                <div class="site-footer__links">
                    <a href="https://jepvenezuela.com" target="_blank" rel="noopener noreferrer" class="site-footer__organization-item">
                        <img src="{{ asset('assets/img/organizations/jep.svg') }}" alt="" class="site-footer__organization-logo">
                        <span class="site-footer__organization-name">JEP Venezuela</span>
                    </a>
                    <a href="https://accesoalajusticia.org" target="_blank" rel="noopener noreferrer" class="site-footer__organization-item">
                        <img src="{{ asset('assets/img/organizations/acceso-justicia-x.png') }}" alt="" class="site-footer__organization-logo">
                        <span class="site-footer__organization-name">Acceso a la Justicia</span>
                    </a>
                    <a href="https://fakenewsvenezuela.org" target="_blank" rel="noopener noreferrer" class="site-footer__organization-item">
                        <img src="{{ asset('assets/img/organizations/fake-news-x-b.png') }}" alt="" class="site-footer__organization-logo">
                        <span class="site-footer__organization-name">Observatorio Fake News</span>
                    </a>
                    <a href="https://observatoriodeuniversidades.com" target="_blank" rel="noopener noreferrer" class="site-footer__organization-item">
                        <img src="{{ asset('assets/img/organizations/obu-b.png') }}" alt="" class="site-footer__organization-logo">
                        <span class="site-footer__organization-name">Observatorio de Universidades</span>
                    </a>
                </div>
            </div>

            <div class="site-footer__col site-footer__column site-footer__status-column">
                <div class="site-footer__status-content">
                    <p class="site-footer__col-title">{{ app()->isLocale('en') ? 'Status' : 'Estado' }}</p>

                    <div class="site-footer__status">
                        <span class="site-footer__status-dot"></span>
                        <span>{{ __('dashboard.data_updated') }}</span>
                    </div>

                    <div class="site-footer__status-divider" aria-hidden="true"></div>

                    <p class="site-footer__sync">
                        {{ __('dashboard.last_sync') }}<br>

                        <strong>
                            {{ filled($lastSync ?? null)
                                ? \Carbon\Carbon::parse($lastSync)
                                    ->setTimezone('America/Caracas')
                                    ->locale(app()->getLocale())
                                    ->translatedFormat('d/m/Y, H:i')
                                : __('dashboard.no_data') }}
                        </strong>
                    </p>

                    <p class="site-footer__timezone">
                        {{ app()->isLocale('en') ? 'Time zone: Venezuela (GMT-4)' : 'Horario: Venezuela (GMT-4)' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Pulso Venezuela.</p>

            <div class="site-footer__language" aria-label="{{ __('dashboard.language') }}">
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}" lang="es">ES</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}" lang="en">EN</a>
            </div>
        </div>
    </div>
</footer>
