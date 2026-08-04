<?php

return [
    'cache_ttl' => (int) env('DASHBOARD_CACHE_TTL', 900),

    'organizations' => [
        ['slug' => 'jep', 'name' => 'Justicia, Encuentro y Perdón', 'x_username' => 'jepvzla', 'website_url' => 'https://jepvenezuela.com', 'logo_path' => 'assets/img/organizations/jep.svg', 'x_logo_path' => 'assets/img/organizations/jep.svg', 'color' => '#dc3545', 'position' => 1],
        ['slug' => 'acceso-justicia', 'name' => 'Acceso a la Justicia', 'x_username' => 'AccesoAJusticia', 'website_url' => 'https://accesoalajusticia.org', 'logo_path' => 'assets/img/organizations/acceso-justicia.png', 'x_logo_path' => 'assets/img/organizations/acceso-justicia-x.png', 'color' => '#0d6efd', 'position' => 2],
        ['slug' => 'fake-news', 'name' => 'Observatorio Venezolano de Fake News', 'x_username' => 'observatoriofn', 'website_url' => 'https://fakenewsvenezuela.org', 'logo_path' => 'assets/img/organizations/fake-news-a.webp', 'x_logo_path' => 'assets/img/organizations/fake-news-x.png', 'color' => '#f59e0b', 'position' => 3],
        ['slug' => 'universidades', 'name' => 'Observatorio de Universidades', 'x_username' => 'obuvenezuela', 'website_url' => 'https://observatoriodeuniversidades.com', 'logo_path' => 'assets/img/organizations/obu.png', 'x_logo_path' => 'assets/img/organizations/obu-x.png', 'color' => '#7c3aed', 'position' => 4],
    ],

    'stats' => [
        ['key' => 'political_prisoners', 'value' => 1875, 'change' => 5.2, 'icon' => 'bi-people-fill', 'sentiment' => 'negative'],
        ['key' => 'women', 'value' => 234, 'change' => 6.4, 'icon' => 'bi-gender-female', 'sentiment' => 'negative'],
        ['key' => 'seriously_ill', 'value' => 142, 'change' => 18.3, 'icon' => 'bi-heart-pulse-fill', 'sentiment' => 'negative'],
        ['key' => 'foreign_dual_nationals', 'value' => 23, 'change' => 21.1, 'icon' => 'bi-globe', 'sentiment' => 'negative'],
        ['key' => 'releases', 'value' => 87, 'change' => 12.9, 'icon' => 'bi-unlock-fill', 'sentiment' => 'positive'],
    ],
];
