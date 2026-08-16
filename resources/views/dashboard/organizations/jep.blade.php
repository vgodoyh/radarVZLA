@extends('layouts.public')

@section('title', __('dashboard.jep_page.meta_title'))

@section('content')
    @php
        $keyFigures = [
            ['number' => '1', 'label' => __('dashboard.jep_page.figures.total'), 'value' => '1.875', 'change' => '↑ 5,2%', 'tone' => 'danger', 'icon' => 'bi-people'],
            ['number' => '2', 'label' => __('dashboard.jep_page.figures.women'), 'value' => '234', 'change' => '↑ 6,4%', 'tone' => 'danger', 'icon' => 'bi-gender-female'],
            ['number' => '3', 'label' => __('dashboard.jep_page.figures.seriously_ill'), 'value' => '142', 'change' => '↑ 18,3%', 'tone' => 'danger', 'icon' => 'bi-heart-pulse'],
            ['number' => '4', 'label' => __('dashboard.jep_page.figures.foreign'), 'value' => '23', 'change' => '↑ 21,1%', 'tone' => 'danger', 'icon' => 'bi-globe-americas'],
            ['number' => '5', 'label' => __('dashboard.jep_page.figures.releases'), 'value' => '87', 'change' => '↑ 12,9%', 'tone' => 'success', 'icon' => 'bi-unlock'],
        ];

        $criteria = collect(__('dashboard.jep_page.criteria.items'))->map(fn (string $text, int $index) => [
            'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'text' => $text,
        ]);

        $detentionCenters = [
            ['name' => __('dashboard.jep_page.centers.helicoide'), 'value' => 231],
            ['name' => __('dashboard.jep_page.centers.ramo_verde'), 'value' => 97],
            ['name' => __('dashboard.jep_page.centers.rodeo'), 'value' => 81],
        ];

        $custodyDeaths = [
            ['letter' => 'A', 'label' => __('dashboard.jep_page.indicators.house_arrest'), 'value' => 2],
            ['letter' => 'B', 'label' => __('dashboard.jep_page.indicators.detention_facilities'), 'value' => 7],
            ['letter' => 'C', 'label' => __('dashboard.jep_page.indicators.hospitals'), 'value' => 1],
        ];

        $vulnerableGroups = [
            ['letter' => 'A', 'label' => __('dashboard.jep_page.indicators.journalists'), 'value' => 42],
            ['letter' => 'B', 'label' => __('dashboard.jep_page.indicators.defenders'), 'value' => 18],
            ['letter' => 'C', 'label' => __('dashboard.jep_page.indicators.unionists'), 'value' => 31],
        ];
    @endphp

    <div class="jep-page">
        @include('dashboard.organizations.jep.hero')

        <main class="jep-page__main">
            <div class="jep-page__container">
                <div class="jep-page__primary-grid">
                    <div class="jep-page__primary-column">
                        @include('dashboard.organizations.jep.key-figures')
                        @include('dashboard.organizations.jep.methodology-releases')
                    </div>

                    @include('dashboard.organizations.jep.criteria')
                </div>

                @include('dashboard.organizations.jep.indicators')

                <div class="jep-page__analysis-grid">
                    @include('dashboard.organizations.jep.trends')
                    @include('dashboard.organizations.jep.detention-centers')
                </div>

                <div class="jep-page__support-grid">
                    @include('dashboard.organizations.jep.methodology-detentions')
                    @include('dashboard.organizations.jep.resources')
                </div>
            </div>
        </main>

        <section class="jep-page__information-strip">
            <div class="jep-page__container">
                <p>{{ __('dashboard.jep_page.dynamic_figures') }}</p>
                <p>{{ __('dashboard.jep_page.organization_period') }}</p>
            </div>
        </section>
    </div>

    @include('dashboard.partials.organization-footer', [
        'footerOrganization' => $organization,
        'footerCategory' => __('dashboard.jep_page.badge'),
        'footerAccent' => '#1769f6',
    ])
@endsection
