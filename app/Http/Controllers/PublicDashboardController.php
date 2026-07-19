<?php

namespace App\Http\Controllers;

use App\Services\FakeNewsVenezuelaService;
use App\Services\AccesoJusticiaService;
use App\Services\TwitterService;
use Illuminate\Support\Facades\Http;

use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    public function index(
                        TwitterService $twitterService,
                        FakeNewsVenezuelaService $fakeNewsVenezuelaService,
                        //AccesoJusticiaService $accesoJusticiaService
                    ): View 
    {
        $stats = [
            [
                'label' => __('dashboard.stats.political_prisoners'),
                'value' => '1.875',
                'change' => '+5,2%',
                'icon' => 'bi-people-fill',
                'trend' => 'up',
            ],
            [
                'label' => __('dashboard.stats.new_detentions'),
                'value' => '142',
                'change' => '+18,3%',
                'icon' => 'bi-person-lock',
                'trend' => 'up-danger',
            ],
            [
                'label' => __('dashboard.stats.women'),
                'value' => '234',
                'change' => '+6,4%',
                'icon' => 'bi-gender-female',
                'trend' => 'up',
            ],
            [
                'label' => __('dashboard.stats.murders'),
                'value' => '23',
                'change' => '+21,1%',
                'icon' => 'bi-droplet-fill',
                'trend' => 'up-danger',
            ],
            [
                'label' => __('dashboard.stats.releases'),
                'value' => '87',
                'change' => '+12,9%',
                'icon' => 'bi-unlock-fill',
                'trend' => 'up',
            ],
        ];

        $organizations = collect([
            [
                'name' => 'Observatorio Venezolano de Fake News',
                'username' => 'observatoriofn',
                'logo' => asset(
                    'assets/img/organizations/fake-news.png'
                ),
                'logo_x' => asset(
                    'assets/img/organizations/fake-news-x.png'
                ),
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Acceso a la Justicia',
                'username' => 'AccesoAJusticia',
                'logo' => asset(
                    'assets/img/organizations/logo-acceso-justicia.png'
                ),
                'logo_x' => asset(
                    'assets/img/organizations/acceso-justicia-x.png'
                ),
                'color' => '#0d6efd',
            ],
            [
                'name' => 'Justicia, Encuentro y Perdón',
                'username' => 'jepvzla',
                'logo' => asset(
                    'assets/img/organizations/jep.svg'
                ),
                'logo_x' => asset(
                    'assets/img/organizations/jep.svg'
                ),
                'color' => '#dc3545',
            ],
            [
                'name' => 'Observatorio de Universidades',
                'username' => 'obuvenezuela',
                'logo' => asset(
                    'assets/img/organizations/obu-blanco.png'
                ),
                'logo_x' => asset(
                    'assets/img/organizations/obu-x.png'
                ),
                'color' => '#7c3aed',
            ],
        ])->map(function (array $organization) use ($twitterService) {
            $organization['posts'] = $twitterService->getLatestPosts(
                $organization['username'],
                7
            );

            return $organization;
        });

        /*Publicaciones de X del Acceso a la Justicia #AlertaLegal. */
        $response = Http::timeout(15)
            ->connectTimeout(5)
            ->retry(2, 500)
            ->withHeaders([
                'X-RapidAPI-Key' => config('services.twitter.key'),
                'X-RapidAPI-Host' => config('services.twitter.host'),
            ])
            ->get('https://twitter-api45.p.rapidapi.com/search.php', [
                'query' => 'from:AccesoAJusticia #AlertaLegal',
                'search_type' => 'Latest',
            ]);

        $alertasLegales = collect($response->json('timeline', []))
            ->take(7)
            ->map(function (array $post) {
                $username = $post['screen_name'] ?? 'AccesoAJusticia';
                $tweetId = $post['tweet_id'] ?? null;

                return [
                    'id' => $tweetId,
                    'text' => $post['text'] ?? '',
                    'created_at' => $post['created_at'] ?? null,
                    'likes' => $post['favorites'] ?? 0,
                    'retweets' => $post['retweets'] ?? 0,
                    'image' => $post['media']['photo'][0]['media_url_https']
                        ?? $post['media']['photo'][0]['media_url']
                        ?? null,
                    'url' => $tweetId
                        ? "https://x.com/{$username}/status/{$tweetId}"
                        : "https://x.com/{$username}",
                ];
            })
            ->values();
            
         /*Publicaciones del sitio web.
        * Devuelve:
        * $postsAccesoJusticia['prensa']
        * $postsAccesoJusticia['persecucion_politica']*/
        //$postsAccesoJusticia = $accesoJusticiaService->getLatestPosts(4);


        /*Publicaciones de X del Observatorio Venezolano de Fake News.*/
        $postsFakeNewsX = $twitterService->getLatestPosts(
            'observatoriofn',7
        );

        /*Publicaciones del sitio web.
        * Devuelve:
        * $postsFakeNewsWeb['en_profundidad']
        * $postsFakeNewsWeb['noti_fake']*/
        $postsFakeNewsWeb = $fakeNewsVenezuelaService->getLatestPosts(4);

        $economicSocialItems = [
            [
                'label' => 'Salario digno',
                'icon' => 'bi-cash-coin',
                'value' => 150,
            ],
            [
                'label' => 'Daños a infraestructura',
                'icon' => 'bi-building',
                'value' => 98,
            ],
            [
                'label' => 'Providencias estudiantiles',
                'icon' => 'bi-mortarboard',
                'value' => 62,
            ],
        ];

        $civilPoliticalItems = [
            [
                'label' => 'Autonomía universitaria',
                'icon' => 'bi-shield-check',
                'value' => 124,
            ],
            [
                'label' => 'Elecciones',
                'icon' => 'bi-check2-square',
                'value' => 76,
            ],
            [
                'label' => 'Reunión pacífica',
                'icon' => 'bi-people',
                'value' => 53,
            ],
        ];

        $years = ['2020', '2021', '2022', '2023', '2024'];

        $protestsData = [120, 260, 410, 240, 300];
        $complaintsData = [95, 200, 380, 210, 330];
        $economicSocialData = [60, 110, 190, 120, 150];
        $civilPoliticalData = [35, 90, 190, 90, 180];

        return view(
            'dashboard.index',
            compact(
                'stats',
                'organizations',
                'economicSocialItems',
                'civilPoliticalItems',
                'alertasLegales',
                //'postsAccesoJusticia',
                'postsFakeNewsX',
                'postsFakeNewsWeb',
                'years',
                'protestsData',
                'complaintsData',
                'economicSocialData',
                'civilPoliticalData'
            )
        );
    }
}