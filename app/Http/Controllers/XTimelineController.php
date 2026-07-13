<?php

// ============================================================
// CONTROLLER: app/Http/Controllers/XTimelineController.php
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class XTimelineController extends Controller
{
    // Pegá tu RapidAPI Key acá
    private string $rapidApiKey = '8684235de3msh002a8818d2786e4p1af34bjsn11cba7c258f0';
    private string $rapidApiHost = 'twitter-api45.p.rapidapi.com';

    private array $accounts = [
        'AccesoaJusticia',
        'observatoriofn',
        'jepvzla',
        'OBUVenezuela',
    ];

    /**
     * Trae el timeline de UNA cuenta EN VIVO (llamando a la API directo).
     * Útil para pruebas puntuales sin esperar al scheduler.
     * Uso: /test-x-rapid/AccesoaJusticia
     */
    public function test(string $username = 'AccesoaJusticia', int $count = 5)
    {
        $response = Http::withHeaders([
            'X-RapidAPI-Key'  => $this->rapidApiKey,
            'X-RapidAPI-Host' => $this->rapidApiHost,
        ])->get("https://{$this->rapidApiHost}/timeline.php", [
            'screenname' => $username,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error'  => 'La API devolvió un error',
                'status' => $response->status(),
                'body'   => $response->json() ?? $response->body(),
            ], $response->status());
        }

        $tweets = $this->parseTweets($response->json(), $username, $count);

        return response()->json([
            'username' => $username,
            'count'    => $tweets->count(),
            'tweets'   => $tweets,
        ]);
    }

    /**
     * Lee el timeline de las 4 cuentas ONG desde el CACHE,
     * que es llenado 3 veces al día por el Command UpdateXTimelines
     * (vía scheduler: 8am, 2pm, 8pm). No llama a la API directamente,
     * así que esta ruta se puede visitar todas las veces que quieras
     * sin gastar requests extra.
     * Uso: /dashboard-x-feeds
     */
    public function dashboardFeeds()
    {
        $allFeeds = [];

        foreach ($this->accounts as $username) {
            $cached = Cache::get("x_timeline_{$username}");

            $allFeeds[$username] = $cached ?? [
                'error'      => true,
                'tweets'     => [],
                'updated_at' => null,
                'message'    => 'Sin datos en cache todavía — corré "php artisan x:update-timelines" al menos una vez.',
            ];
        }

        return response()->json($allFeeds);
    }

    /**
     * Parsea la respuesta cruda de twitter-api45:
     * - Filtra retweets (texto que empieza con "RT @")
     * - Extrae imagen si existe (media.photo[0])
     * - Devuelve solo $count tweets ya limpios
     */
    private function parseTweets(array $data, string $username, int $count)
    {
        return collect($data['timeline'] ?? [])
            ->reject(fn($t) => str_starts_with($t['text'] ?? '', 'RT @'))
            ->take($count)
            ->map(function ($t) use ($username) {
                $image = $t['media']['photo'][0]['media_url_https'] ?? null;

                return [
                    'id'       => $t['tweet_id'] ?? null,
                    'text'     => $t['text'] ?? '',
                    'date'     => $t['created_at'] ?? null,
                    'likes'    => $t['favorites'] ?? 0,
                    'retweets' => $t['retweets'] ?? 0,
                    'image'    => $image,
                    'url'      => isset($t['tweet_id'])
                        ? "https://x.com/{$username}/status/{$t['tweet_id']}"
                        : null,
                ];
            })
            ->values();
    }
}


// ============================================================
// RUTAS: routes/web.php (sin cambios respecto a antes)
// ============================================================
//
// use App\Http\Controllers\XTimelineController;
//
// Route::get('/test-x-rapid/{username?}', [XTimelineController::class, 'test']);
// Route::get('/dashboard-x-feeds', [XTimelineController::class, 'dashboardFeeds']);
//
// NOTA: dashboardFeeds() ahora SOLO lee cache, nunca llama la API.
// Quien llena ese cache es app/Console/Commands/UpdateXTimelines.php,
// corriendo 3x al día vía el scheduler (routes/console.php).
//
// Para tener datos la primera vez, corré manualmente:
// php artisan x:update-timelines
// ============================================================