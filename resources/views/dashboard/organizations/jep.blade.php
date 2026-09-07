@extends('layouts.public_v2')

@section('title', __('dashboard.jep_page.meta_title'))

@section('content')
    @php
        $jepSnapshot = $jepMetrics;
        $formatPeriod = static function ($snapshot, string $prefix): string {
            $startMonth = $snapshot?->{$prefix.'_start_month'};
            $startYear = $snapshot?->{$prefix.'_start_year'};
            $endMonth = $snapshot?->{$prefix.'_end_month'};
            $endYear = $snapshot?->{$prefix.'_end_year'};
            if (! $startMonth || ! $startYear || ! $endMonth || ! $endYear) return 'Período no disponible';
            $start = \Carbon\Carbon::create($startYear, $startMonth, $snapshot?->{$prefix.'_start_day'} ?: 1)->locale('es');
            $end = \Carbon\Carbon::create($endYear, $endMonth, $snapshot?->{$prefix.'_end_day'} ?: 1)->locale('es');
            return $snapshot?->{$prefix.'_start_day'} && $snapshot?->{$prefix.'_end_day'}
                ? $start->isoFormat('D MMM').' – '.$end->isoFormat('D MMM YYYY')
                : $start->isoFormat('MMM YYYY').' – '.$end->isoFormat('MMM YYYY');
        };
        $keyFigures = [
            ['number' => '1', 'label' => __('dashboard.jep_page.figures.total'), 'value' => $jepSnapshot?->total_political_prisoners ?? 0, 'change' => '', 'tone' => 'danger', 'icon' => 'bi-people'],
            ['number' => '2', 'label' => __('dashboard.jep_page.figures.women'), 'value' => $jepSnapshot?->women ?? 0, 'change' => '', 'tone' => 'danger', 'icon' => 'bi-gender-female'],
            ['number' => '3', 'label' => __('dashboard.jep_page.figures.seriously_ill'), 'value' => $jepSnapshot?->seriously_ill ?? 0, 'change' => '', 'tone' => 'danger', 'icon' => 'bi-heart-pulse'],
            ['number' => '4', 'label' => __('dashboard.jep_page.figures.foreign'), 'value' => $jepSnapshot?->foreign_or_dual_nationality ?? 0, 'change' => '', 'tone' => 'danger', 'icon' => 'bi-globe-americas'],
            ['number' => '5', 'label' => __('dashboard.jep_page.figures.releases'), 'value' => $jepSnapshot?->releases ?? 0, 'change' => $formatPeriod($jepSnapshot, 'releases_period'), 'tone' => 'success', 'icon' => 'bi-unlock'],
        ];
        $criteria = collect(__('dashboard.jep_page.criteria.items'))->map(fn (string $text, int $index) => ['number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), 'text' => $text]);
        $detentionCenters = $jepSnapshot?->detentionCenters?->map(fn ($center) => $center->only(['name', 'value']))->all() ?? [];
        $vulnerableGroups = $jepSnapshot?->vulnerableGroups?->map(fn ($group) => $group->only(['label', 'value']))->all() ?? [];
        $jepDeathsPeriod = $formatPeriod($jepSnapshot, 'deaths_period');
        $jepMonthlyAlertTitle = $jepSnapshot?->monthly_alert_title ?: __('dashboard.jep_page.indicators.monthly_alert');
        $jepMonthlyAlertExcerpt = $jepSnapshot?->monthly_alert_excerpt ?: __('dashboard.jep_page.indicators.alert_text');
        $jepMonthlyAlertXUrl = $jepSnapshot?->monthly_alert_x_url;
    @endphp

    <div class="jep-page">
        @include('dashboard.partials.global-header', ['headerAccent' => '#1769f6'])
        @include('dashboard.organizations.jep.hero')
        @include('dashboard.organizations.jep.key-figures')
        <main class="jep-page__main">
            <div class="jep-page__container">
                <div class="jep-page__primary-grid">
                    <div class="jep-page__primary-column">@include('dashboard.organizations.jep.methodology-releases')</div>
                    @include('dashboard.organizations.jep.criteria')
                </div>
                @include('dashboard.organizations.jep.indicators')
                <article id="alerta-del-mes" class="jep-indicator-card jep-indicator-card--alert jep-page__monthly-alert">
                    <span></span>
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <div class="jep-indicator-card--alert__content">
                        <p>{{ $jepMonthlyAlertTitle }}</p>
                        @if (filled($jepMonthlyAlertExcerpt))
                            <blockquote class="jep-monthly-alert__text">{{ $jepMonthlyAlertExcerpt }}</blockquote>
                        @endif
                        @if (filled($jepMonthlyAlertXUrl))
                            <div class="jep-monthly-alert__footer">
                                <a class="jep-monthly-alert__external" href="{{ $jepMonthlyAlertXUrl }}" target="_blank" rel="noopener noreferrer">Ver publicación original en X <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                            </div>
                        @endif
                    </div>
                </article>
                @include('dashboard.organizations.jep.trends')
            </div>
        </main>
        <section class="jep-page__information-strip"><div class="jep-page__container"><p>{{ __('dashboard.jep_page.dynamic_figures') }}</p><p>{{ __('dashboard.jep_page.organization_period') }}</p></div></section>
    </div>

    @include('dashboard.partials.organization-footer', ['footerOrganization' => $organization, 'footerCategory' => __('dashboard.jep_page.badge'), 'footerAccent' => '#1769f6', 'footerLinks' => ['website' => 'https://www.jepvenezuela.com', 'contact' => 'https://www.jepvenezuela.com', 'info' => 'https://www.jepvenezuela.com/quienes-somos/', 'facebook' => 'https://www.facebook.com/JEPVenezuela', 'x' => 'https://x.com/jepvzla', 'instagram' => 'https://www.instagram.com/jepvzla', 'youtube' => '', 'tiktok' => '', 'telegram' => '']])
@endsection
