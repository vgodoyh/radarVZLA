<footer class="site-footer">
    <div class="site-footer__container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <div class="site-footer__logo-row">
                    <img
                        src="{{ asset('assets/img/isotipo-pulso.png') }}"
                        alt="Pulso Venezuela"
                        class="site-footer__logo"
                    >
                </div>

                <p>{{ __('dashboard.footer_description') }}</p>
            </div>

            <div class="site-footer__col">
                <p class="site-footer__col-title">{{ __('dashboard.name_organizations') }}</p>

                <div class="site-footer__links">
                    <a href="https://jepvenezuela.com" target="_blank" rel="noopener noreferrer">
                        JEP Venezuela
                    </a>
                    <a href="https://accesoalajusticia.org" target="_blank" rel="noopener noreferrer">
                        Acceso a la Justicia
                    </a>
                    <a href="https://fakenewsvenezuela.org" target="_blank" rel="noopener noreferrer">
                        Observatorio Fake News
                    </a>
                    <a href="https://observatoriodeuniversidades.com" target="_blank" rel="noopener noreferrer">
                        Observatorio de Universidades
                    </a>
                </div>
            </div>

            <div class="site-footer__col">
                <div class="site-footer__status">
                    <span class="site-footer__status-dot"></span>
                    <span>{{ __('dashboard.data_updated') }}</span>
                </div>

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
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Pulso Venezuela.</p>
        </div>
    </div>
</footer>
