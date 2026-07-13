<?php

// ============================================================
// 1. COMMAND: app/Console/Commands/UpdateXTimelines.php
// ============================================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateXTimelines extends Command
{
    protected $signature = 'x:update-timelines';
    protected $description = 'Actualiza los timelines de X de las 4 cuentas ONG (corre 3x al día via scheduler)';

    private string $rapidApiKey = '8684235de3msh002a8818d2786e4p1af34bjsn11cba7c258f0';
    private string $rapidApiHost = 'twitter-api45.p.rapidapi.com';

    private array $accounts = [
        'AccesoaJusticia',
        'observatoriofn',
        'jepvzla',
        'OBUVenezuela',
    ];

    public function handle()
    {
        $this->info('Actualizando timelines de X...');

        foreach ($this->accounts as $username) {
            $this->line("→ Consultando @{$username}...");

            $response = Http::withHeaders([
                'X-RapidAPI-Key'  => $this->rapidApiKey,
                'X-RapidAPI-Host' => $this->rapidApiHost,
            ])->get("https://{$this->rapidApiHost}/timeline.php", [
                'screenname' => $username,
            ]);

            if ($response->failed()) {
                $this->error("  ✗ Error con @{$username}: " . $response->status());
                // No tocamos el cache existente si falla, así seguimos
                // mostrando los últimos datos buenos en el dashboard.
                continue;
            }

            $tweets = $this->parseTweets($response->json(), $username, 5);

            // Guardamos SIN expiración por tiempo — el scheduler decide
            // cuándo se refresca, no un TTL. forever() + se sobreescribe
            // cada vez que corre este comando.
            Cache::forever("x_timeline_{$username}", [
                'error'  => false,
                'tweets' => $tweets,
                'updated_at' => now()->toDateTimeString(),
            ]);

            $this->info("  ✓ @{$username}: " . $tweets->count() . ' tuits guardados');
        }

        $this->info('Listo.');
    }

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
// 2. SCHEDULER: agregar en routes/console.php (Laravel 11+)
//    o en app/Console/Kernel.php método schedule() (Laravel <11)
// ============================================================
//
// --- Laravel 11+ (routes/console.php) ---
//
// use Illuminate\Support\Facades\Schedule;
//
// Schedule::command('x:update-timelines')->dailyAt('08:00');
// Schedule::command('x:update-timelines')->dailyAt('14:00');
// Schedule::command('x:update-timelines')->dailyAt('20:00');
//
// --- Laravel <11 (app/Console/Kernel.php) ---
//
// protected function schedule(Schedule $schedule)
// {
//     $schedule->command('x:update-timelines')->dailyAt('08:00');
//     $schedule->command('x:update-timelines')->dailyAt('14:00');
//     $schedule->command('x:update-timelines')->dailyAt('20:00');
// }
//
// ============================================================
// 3. IMPORTANTE: el scheduler de Laravel necesita un cron real
//    corriendo cada minuto en el servidor para disparar las tareas.
//    En localhost/desarrollo, simulalo corriendo manualmente:
//
//    php artisan x:update-timelines
//
//    En producción, agregar esta línea al crontab del servidor:
//
//    * * * * * cd /ruta-a-tu-proyecto && php artisan schedule:run >> /dev/null 2>&1
// ============================================================