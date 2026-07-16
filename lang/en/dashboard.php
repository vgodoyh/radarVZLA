<?php
return [
    'meta_title'=>'Radar VZLA',
    'public_dashboard'=>'Public dashboard',
    'tagline' => 'Human Rights, Justice, and Disinformation.',
    'tagline_1' => 'Human Rights',
    'tagline_2' => 'Justice',
    'tagline_3' => 'Disinformation',
    'language'=>'Language',

    //FAKE NEWS
    'fake_news' => 'Observatorio Venezolano de Fake News',
    'fakenews_title' => 'Disinformation and fact-checking in Venezuela',
    'noti_fake' => 'Noti-Fake',
    'notifake_title' => '',
    'en_profundidad' => 'In-depth',
    'enprofundidad_title' => 'Monthly analyses, opinion columns, and infographics on the phenomenon of Fake News in Venezuela.',
    'ver_publicacion' => 'Ver publicación',

    //ACCESO JUSTICIA
    'acceso_justicia' => 'Acceso a la Justicia',
    'accesojusticia_title' => 'Rule of Law and Justice Observatory',
    'prensa' => 'News',
    'art_pp' => 'Political Persecution Articles',

    //JUSTICIA, ENCUENTRO Y PERDÓN
    'jep' => 'Justicia, Encuentro y Perdón',
    'jep_title' => 'Human rights and justice in Venezuela',
    

    'fortnight_header'=>'Fortnight header',
    'period'=>'May 1–15, 2026',
    'in_progress'=>'Current fortnight',
    'publish_report'=>'Publish fortnight report',
    'key_figures'=>'Key figures',
    'snapshot'=>'The most important data at a glance',
    'comparison'=>'Compared with the previous fortnight',
    'featured_indicator'=>'Featured indicator of the month',
    'featured_title'=>'Transfer of terrorism-related cases to ordinary courts',
    'featured_analysis'=>'During the first fortnight, transfers of cases from specialized courts to ordinary criminal courts increased. This change is relevant compared with the previous period and requires monitoring because of its impact on due process.',
    'press_release'=>'Press release',
    'x_thread'=>'Thread on Twitter / X',
    'full_website'=>'View full analysis',
    'indicator_groups'=>'Indicator groups',
    'explore_data'=>'Explore the information by dimension',
    'view_indicators'=>'View indicators',

    //OBU
    'universities'=>'Observatorio de Universidades',
    'university_title'=>'University education in Venezuela',
    'university_description'=>'News related to university education in Venezuela.',
    'protests' => 'Protests',
    'complaints' => 'Complaints',
    'economic_social_complaints' => 'Reports of Economic, Social, and Cultural Rights Violations',
    'civil_political_complaints' => 'Reports of Civil and Political Rights Violations',

    'latest_posts'=>'Latest posts',
    'from_x'=>'Recent activity on X',
    'view_on_x'=>'View on X',
    
    'social_media' => 'Social Media',
    'latest_org_posts' => 'Latest Posts from the Organizations',

    

    'footer_description' => 'Human Rights, Justice, and Disinformation. Official monitoring dashboard of Acceso a la Justicia, Observatorio de Fake News, JEP Venezuela, and Observatorio de Universidades, featuring their latest activity on X.',
    'footer_disclaimer' => 'A project by Acceso a la Justicia, Observatorio de Fake News, JEP Venezuela and Observatorio de Universidades.',

    'name_organizations' => 'Organizations',
    'data_updated' => 'Data Updated',
    'last_sync' => 'Last Updated:',

    'stats'=>['political_prisoners'=>'Political prisoners',
              'new_detentions'=>'New detentions',
              'women'=>'Women',
              'murders'=>'Murders',
              'releases'=>'Releases'
            ],
    'organizations'=>[
        ['name'=>'Access to Justice',
        'description'=>'Monitoring the transfer of terrorism-related cases to ordinary criminal courts.',
        'logo' => asset('assets/img/organizations/acceso-justicia.png'),
        'url' => 'https://accesoalajusticia.org',
        ],
        ['name'=>'Venezuelan Fake News Observatory',
         'description'=>'Detection, analysis and digital literacy to strengthen democratic communication.',
         'logo' => asset('assets/img/organizations/fake-news.png'),
         'url' => 'https://fakenewsvenezuela.org',
        ],
        ['name'=>'Justice, Encounter and Forgiveness',
         'description'=>'Documentation and follow-up of arbitrary detentions and killings linked to political persecution.',
         'logo' => asset('assets/img/organizations/jep.svg'),
         'url' => 'https://jepvenezuela.com',
        ],
        ['name'=>'University Observatory',
         'description'=>'News, protests and complaints related to higher education in Venezuela.',
         'logo' => asset('assets/img/organizations/obu.png'),
         'url' => 'https://observatoriodeuniversidades.com',
        ],
    ],
    'groups'=>[
        ['title'=>'Sociodemographic profile','items'=>['Age','Gender','Social group'],'icon'=>'bi bi-people-fill'],
        ['title'=>'Legal situation','items'=>['Charges','Procedural abuses','Access to counsel'],'icon'=>'fa-solid fa-gavel'],
        ['title'=>'Critical health indicators','items'=>['Health conditions','Medical care','Chronic illnesses'],'icon'=>'bi bi-heart-pulse-fill'],
        ['title'=>'Repressive context','items'=>['Responsible actors','Torture','Isolation'],'icon'=>'bi bi-shield-exclamation'],
        ['title'=>'Vulnerable groups and nationality','items'=>['Vulnerable groups','Nationality','Indigenous peoples'],'icon'=>'bi bi-person-hearts'],
        ['title'=>'Geographic distribution','items'=>['By state','Clandestine centers','Transfers'],'icon'=>'bi bi-geo-alt-fill'],
        ['title'=>'Time evolution','items'=>['Trends','Historical peak','Accumulated database'],'icon'=>'bi bi-graph-up-arrow'],
        ['title'=>'Visibility and advocacy','items'=>['Media coverage','UN rapporteur alerts','Statements issued'],'icon'=>'bi bi-megaphone-fill'],
    ],
    'posts'=>[
        ['name'=>'Access to Justice',
         'handle'=>'@AccesoAJusticia',
         'text'=>'Monitoring case transfers and due process guarantees.',
         'icon'=>'bi bi-bank'
        ],
        ['name'=>'Fake News Observatory',
         'handle'=>'@ObservatorioFN',
         'text'=>'We debunk false content and promote digital literacy.',
         'icon'=>'bi bi-shield-check'
        ],
        ['name'=>'Justice, Encounter and Forgiveness',
         'handle'=>'@JEPVzla',
         'text'=>'We document new cases of political persecution and arbitrary detention.',
         'icon'=>'bi bi-people'
        ],
        ['name'=>'University Observatory',
         'handle'=>'@OBUVenezuela',
         'text'=>'The university community reports infrastructure failures and low salaries.',
         'icon'=>'bi bi-mortarboard'
        ],
    ],
    'economicSocialItems' => [
        ['label' => 'Salario digno', 
         'icon'  => 'bi-cash-coin',
         'value' => 150
        ],
        ['label' => 'Daños a infraestructura', 
         'icon' => 'bi-building', 
         'value' => 98
        ],
        ['label' => 'Providencias estudiantiles', 
         'icon' => 'bi-mortarboard', 
         'value' => 62
        ],
    ],

    'civilPoliticalItems' => [
        ['label' => 'Autonomía universitaria', 
         'icon' => 'bi-shield-check', 
         'value' => 124
        ],
        ['label' => 'Elecciones', 
         'icon' => 'bi-check2-square', 
         'value' => 76
        ],
        ['label' => 'Reunión pacífica', 
         'icon' => 'bi-people', 
         'value' => 53
        ],
    ],
];
