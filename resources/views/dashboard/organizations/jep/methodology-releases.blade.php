@php
    $methodologyText = __('dashboard.jep_page.release_methodology.text');
    $methodologySentences = preg_split('/(?<=[.!?])\s+/u', trim($methodologyText), -1, PREG_SPLIT_NO_EMPTY);
    $methodologySecondBlock = count($methodologySentences) > 1 ? array_pop($methodologySentences) : null;
    $methodologyFirstBlock = implode(' ', $methodologySentences);
@endphp

<aside class="jep-methodology">
    <header class="jep-methodology__header">
        <span class="jep-methodology__icon"><i class="bi bi-info-circle" aria-hidden="true"></i></span>
        <div>
            <h2>{{ __('dashboard.jep_page.release_methodology.title') }}</h2>
            <span class="jep-methodology__rule" aria-hidden="true"></span>
        </div>
    </header>
    <div class="jep-methodology__content">
        <div class="jep-methodology__visual" aria-hidden="true">
            <div class="jep-methodology__document">
                <i class="bi bi-file-earmark-text"></i>
                <span></span>
                <span></span>
                <span></span>
                <b><i class="bi bi-shield-check"></i></b>
            </div>
        </div>

        <div class="jep-methodology__copy">
            <p>{{ $methodologyFirstBlock }}</p>
            @if ($methodologySecondBlock)
                <span class="jep-methodology__divider" aria-hidden="true"></span>
                <p>{{ $methodologySecondBlock }}</p>
            @endif
        </div>
    </div>
</aside>
