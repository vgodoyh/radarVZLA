<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    private string $baseUrl = 'https://twitter-api45.p.rapidapi.com';

    public function getLatestPosts(string $username, int $limit = 7): Collection
    {
        $username = ltrim($username, '@');

        $cacheKey = "twitter.latest-posts.{$username}";

        $posts = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($username, $limit): array {
                try {
                    $response = Http::timeout(15)
                        ->connectTimeout(5)
                        ->retry(2, 500)
                        ->withHeaders([
                            'X-RapidAPI-Key' => config('services.twitter.key'),
                            'X-RapidAPI-Host' => config('services.twitter.host'),
                        ])
                        ->get($this->baseUrl . '/timeline.php', [
                            'screenname' => $username,
                        ]);

                    if ($response->failed()) {
                        Log::warning('Error consultando timeline de X', [
                            'username' => $username,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);

                        return [];
                    }

                    return $this->normalizePosts(
                        $response->json(),
                        $username,
                        $limit
                    )->all();

                } catch (\Throwable $exception) {
                    Log::error('Excepción consultando timeline de X', [
                        'username' => $username,
                        'message' => $exception->getMessage(),
                    ]);

                    return [];
                }
            }
        );

        if (! is_array($posts)) {
            Cache::forget($cacheKey);

            return collect();
        }

        return collect($posts);
    }

    private function normalizePosts(
        array $data,
        string $username,
        int $limit
    ): Collection {
        return collect($data['timeline'] ?? [])
            ->filter(fn (array $post) => filled($post['text'] ?? null))
            ->reject(
                fn (array $post) => str_starts_with(
                    $post['text'] ?? '',
                    'RT @'
                )
            )
            ->take($limit)
            ->map(function (array $post) use ($username) {
                $tweetId = $post['tweet_id'] ?? null;

                return [
                    'id' => $tweetId,
                    'text' => $post['text'] ?? '',
                    'created_at' => $post['created_at'] ?? null,
                    'likes' => $post['favorites'] ?? 0,
                    'retweets' => $post['retweets'] ?? 0,
                    'image' => $post['media']['photo'][0]['media_url_https']
                        ?? null,
                    'url' => $tweetId
                        ? "https://x.com/{$username}/status/{$tweetId}"
                        : "https://x.com/{$username}",
                    'category' => $this->getCategory(
                        $post['text'] ?? ''
                    ),
                ];
            })
            ->values();
    }

    private function getCategory(string $text): string
    {
        $text = mb_strtolower($text);

        return match (true) {
            str_contains($text, 'universidad'),
            str_contains($text, 'universitario'),
            str_contains($text, 'educación') => 'Universidades',

            str_contains($text, 'desinformación'),
            str_contains($text, 'noticia falsa'),
            str_contains($text, 'fake news'),
            str_contains($text, 'engañoso') => 'Desinformación',

            str_contains($text, 'detención'),
            str_contains($text, 'preso político'),
            str_contains($text, 'derechos humanos'),
            str_contains($text, 'ddhh') => 'Derechos Humanos',

            default => 'Justicia',
        };
    }
}