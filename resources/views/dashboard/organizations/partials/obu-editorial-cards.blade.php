<article class="obu-editorial-card obu-editorial-card--note">
    <div class="obu-editorial-card__copy">
        <span class="obu-public-eyebrow">Nota mensual</span>
        <small>{{ $obuMonthlyNote['publication_date'] ?? 'Contenido editorial OBU' }}</small>
        <h2>{{ $obuMonthlyNote['title'] ?? 'Nota mensual' }}</h2>
        <p>{{ $obuMonthlyNote['excerpt'] ?? 'La próxima nota mensual del Observatorio de Universidades estará disponible próximamente.' }}</p>
        @if (! empty($obuMonthlyNote['url']))
            <a class="obu-editorial-button obu-editorial-button--note" href="{{ $obuMonthlyNote['url'] }}" target="_blank" rel="noopener noreferrer">Ver nota mensual <i class="bi bi-arrow-up-right"></i></a>
        @endif
    </div>
    <div class="obu-editorial-card__media">
        @if (! empty($obuMonthlyNote['image_path_url']))
            <img src="{{ $obuMonthlyNote['image_path_url'] }}" alt="{{ $obuMonthlyNote['title'] ?? 'Nota mensual del OBU' }}" loading="lazy">
        @else
            <div class="obu-editorial-media-placeholder" role="img" aria-label="Imagen de la nota">
                <i class="bi bi-file-earmark-image" aria-hidden="true"></i>
                <span>Imagen de la nota</span>
            </div>
        @endif
    </div>
</article>

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
