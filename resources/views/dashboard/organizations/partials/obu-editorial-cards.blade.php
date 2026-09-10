<article class="obu-editorial-card obu-editorial-card--note">
    <div class="obu-editorial-card__copy">
        <span class="obu-public-eyebrow">{{ __('dashboard.obu.monthly_note') }}</span>
        <small>{{ __('dashboard.obu.editorial_content') }}</small>
        <h2>Las continuas violaciones a los derechos laborales movilizan a los universitarios en 2026</h2>
        <p>El OBU registró 59 protestas y 57 denuncias por la exigencia de salarios justos para los universitarios durante los primeros cinco meses de 2026...</p>
        <a class="obu-editorial-button obu-editorial-button--note" href="https://observatoriodeuniversidades.com/noticias-obu-las-continuas-violaciones-a-los-derechos-laborales-movilizan-a-los-universitarios-en-2026/" target="_blank" rel="noopener noreferrer">{{ __('dashboard.obu.read_full_note') }} <i class="bi bi-arrow-up-right"></i></a>
    </div>
    <div class="obu-editorial-card__media">
        <img src="{{ asset('assets/img/nota-prensa-obu.jpg') }}" alt="Nota mensual del OBU" loading="lazy">
    </div>
</article>

{{-- TODO: Reactivar Alerta bimensual cuando exista contenido editorial.
<article class="obu-editorial-card obu-editorial-card--alert">
    <div class="obu-editorial-card__copy">
        <span class="obu-public-eyebrow">Alerta bimensual</span>
        <small>
            @if ($obuBimonthlyAlert)
                {{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_start'])->locale('es')->isoFormat('MMMM') }} – {{ \Carbon\Carbon::parse($obuBimonthlyAlert['period_end'])->locale('es')->isoFormat('MMMM YYYY') }}
            @else
                Contenido editorial OBU
            @endif
        </small>
        <h2>{{ $obuBimonthlyAlert['title'] ?? 'Alerta bimensual' }}</h2>
        <p>{{ $obuBimonthlyAlert['excerpt'] ?? 'La próxima alerta bimensual del Observatorio de Universidades estará disponible próximamente.' }}</p>
        @if (! empty($obuBimonthlyAlert['url']))
            <a class="obu-editorial-button obu-editorial-button--alert" href="{{ $obuBimonthlyAlert['url'] }}" target="_blank" rel="noopener noreferrer">Ver alerta bimensual <i class="bi bi-arrow-up-right"></i></a>
        @endif
    </div>
    <div class="obu-editorial-card__media">
        @if (! empty($obuBimonthlyAlert['image_path_url']))
            <img src="{{ $obuBimonthlyAlert['image_path_url'] }}" alt="{{ $obuBimonthlyAlert['title'] ?? 'Alerta bimensual del OBU' }}" loading="lazy">
        @else
            <div class="obu-editorial-media-placeholder obu-editorial-media-placeholder--alert" role="img" aria-label="Imagen de la alerta">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <span>Imagen de la alerta</span>
            </div>
        @endif
    </div>
</article>
--}}
