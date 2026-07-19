<?php
return [
    'meta_title'=>'Radar VZLA',
    'public_dashboard'=>'Dashboard público',
    'tagline' => 'Derechos Humanos, Justicia y Desinformación.',
    'tagline_1' => 'Derechos Humanos',
    'tagline_2' => 'Justicia',
    'tagline_3' => 'Desinformación',
    'language'=>'Idioma',

    //FAKE NEWS
    'fake_news' => 'Observatorio Venezolano de Fake News',
    'fakenews_title' => 'Desinformación y verificación en Venezuela',
    'noti_fake' => 'Noti Fake',
    'notifake_title' => 'Análisis, columnas e infografías mensuales que abordan el fenómeno de las Fake News en Venezuela.',
    'en_profundidad' => 'En profundidad',
    'enprofundidad_title' => 'Análisis, columnas e infografías mensuales que abordan el fenómeno de las Fake News en Venezuela.',
    'ver_publicacion' => 'Ver publicación',
    

    //ACCESO JUSTICIA
    'acceso_justicia' => 'Acceso a la Justicia',
    'accesojusticia_title' => 'Observatorio de Derecho y Justicia',
    'prensa' => 'Notas de prensa',
    'art_pp' => 'Artículos de persecusión política',

    //JUSTICIA, ENCUENTRO Y PERDÓN
    'jep' => 'Justicia, Encuentro y Perdón',
    'jep_title' => 'Derechos humanos y justicia en Venezuela',

    'fortnight_header'=>'Cabecera de quincena',
    'period'=>'1 – 15 de mayo de 2026',
    'in_progress'=>'Quincena en curso',
    'publish_report'=>'Publicar informe de quincena',
    'key_figures'=>'Cifras clave',
    'snapshot'=>'Los datos más importantes de un vistazo',
    'comparison'=>'Comparación con la quincena anterior',
    'featured_indicator'=>'Indicador destacado del mes',
    'featured_title'=>'Traslados de causas por terrorismo a tribunales ordinarios',
    'featured_analysis'=>'Durante la primera quincena se registró un aumento de traslados de causas desde juzgados especializados hacia tribunales ordinarios. El comportamiento representa una variación relevante respecto al período anterior y requiere seguimiento por su impacto en el debido proceso.',
    'press_release'=>'Nota de prensa',
    'x_thread'=>'Hilo en Twitter / X',
    'full_website'=>'Ver análisis completo',
    'indicator_groups'=>'Grupos de indicadores',
    'explore_data'=>'Explora la información por dimensión',
    'view_indicators'=>'Ver indicadores',

    //OBU
    'universities'=>'Observatorio de Universidades',
    'university_title'=>'Educación universitaria en Venezuela',
    'university_description'=>'Noticias referidas a la educación universitaria en Venezuela.',
    'protests' => 'Protestas',
    'complaints' => 'Denuncias',    
    'economic_social_complaints' => 'Denuncias económicas, sociales y culturales',
    'civil_political_complaints' => 'Denuncias por derechos políticos y civiles',

    'latest_posts'=>'Últimos posts',
    'last_post'=>'Último post',
    'from_x'=>'Actividad reciente en X',
    'view_on_x'=>'Ver en X',

    'social_media' => 'Redes sociales',
    'latest_org_posts' => 'Lo que están publicando las organizaciones',

    

    'footer_description' => 'Derechos Humanos, Justicia y Desinformación. Panel de seguimiento oficial de Acceso a la Justicia, Observatorio de Fake News, JEP Venezuela y OBU, con su actividad reciente en X e Instagram.',
    'footer_disclaimer' => 'Un proyecto de Acceso a la Justicia, Observatorio de Fake News, JEP Venezuela y OBU.',
    'name_organizations' => 'Organizaciones',
    'data_updated' => 'Datos actualizados',
    'last_sync' => 'Última sincronización:',

    'stats'=>['political_prisoners'=>'Total de presos políticos',
              'new_detentions'=>'Nuevas detenciones',
              'women'=>'Mujeres',
              'murders'=>'Asesinatos',
              'releases'=>'Liberaciones'
             ],
    'organizations'=>[
        ['name'=>'Acceso a la Justicia',
         'description'=>'Seguimiento al traslado de causas vinculadas a delitos de terrorismo hacia tribunales ordinarios penales.',
         'logo' => asset('assets/img/organizations/acceso-justicia.png'),
         'url' => 'https://accesoalajusticia.org',
        ],
        ['name'=>'Observatorio Venezolano de Fake News',
         'description'=>'Detección, análisis y alfabetización digital para fortalecer la comunicación democrática.',
         'logo' => asset('assets/img/organizations/fake-news.png'),
         'url' => 'https://fakenewsvenezuela.org',
        ],
        ['name'=>'Justicia, Encuentro y Perdón',
         'description'=>'Registro y seguimiento de detenciones arbitrarias y asesinatos por persecución política.',
         'logo' => asset('assets/img/organizations/jep.svg'),
         'url' => 'https://jepvenezuela.com',
        ],
        ['name'=>'Observatorio de Universidades',
         'description'=>'Noticias, protestas y denuncias vinculadas con la educación universitaria en Venezuela.',
         'logo' => asset('assets/img/organizations/obu.png'),
         'url' => 'https://observatoriodeuniversidades.com',
        ],
    ],

    'groups'=>[
        ['title'=>'Perfil sociodemográfico',
         'items'=>['Edad','Género','Grupo social'],
         'icon'=>'bi bi-people-fill'],

        ['title'=>'Situación jurídica',
         'items'=>['Delitos imputados','Abusos procesales','Acceso a abogado'],
         'icon'=>'fa-solid fa-gavel'],

        ['title'=>'Indicadores críticos de salud',
         'items'=>['Condiciones de salud','Atención médica','Enfermedades crónicas'],
         'icon'=>'bi bi-heart-pulse-fill'],

        ['title'=>'Contexto represivo',
         'items'=>['Responsables','Torturas','Aislamiento'],
         'icon'=>'bi bi-shield-exclamation'],

        ['title'=>'Grupos vulnerables y nacionalidad',
         'items'=>['Grupos vulnerables','Nacionalidad','Pueblos indígenas'],
         'icon'=>'bi bi-person-hearts'],

        ['title'=>'Distribución geográfica',
         'items'=>['Por estado','Centros clandestinos','Traslados'],
         'icon'=>'bi bi-geo-alt-fill'],

        ['title'=>'Evolución temporal',
         'items'=>['Tendencias','Pico histórico','Base acumulada'],
         'icon'=>'bi bi-graph-up-arrow'],

        ['title'=>'Visibilidad e incidencia',
         'items'=>['Cobertura mediática','Alertas a relatores ONU','Comunicados'],
         'icon'=>'bi bi-megaphone-fill'],
    ],
    'posts'=>[
        ['name'=>'Acceso a la Justicia',
         'handle'=>'@AccesoAJusticia',
         'text'=>'Seguimiento a los traslados de causas y garantías del debido proceso.',
         'icon'=>'bi bi-bank'],

        ['name'=>'Observatorio Fake News',
         'handle'=>'@ObservatorioFN',
         'text'=>'Desmentimos contenidos falsos y promovemos alfabetización digital.',
         'icon'=>'bi bi-shield-check'],

        ['name'=>'Justicia, Encuentro y Perdón',
         'handle'=>'@JEPVzla',
         'text'=>'Documentamos nuevos casos de persecución política y detenciones arbitrarias.',
         'icon'=>'bi bi-people'],

        ['name'=>'Observatorio de Universidades',
         'handle'=>'@OBUVenezuela',
         'text'=>'La comunidad universitaria denuncia fallas de infraestructura y bajos salarios.',
         'icon'=>'bi bi-mortarboard'],
    ],

    'economicSocialItems' => [
        ['label' => 'Salario digno', 'icon' => 'bi-cash-coin'],
        ['label' => 'Daños a infraestructura', 'icon' => 'bi-building'],
        ['label' => 'Providencias estudiantiles', 'icon' => 'bi-mortarboard'],
    ],

    'civilPoliticalItems' => [
        ['label' => 'Autonomía universitaria', 'icon' => 'bi-shield-check'],
        ['label' => 'Elecciones', 'icon' => 'bi-check2-square'],
        ['label' => 'Reunión pacífica', 'icon' => 'bi-people'],
    ],
];
