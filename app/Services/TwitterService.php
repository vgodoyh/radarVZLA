<?php

namespace App\Services;

use Illuminate\Support\Carbon;
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

        $cacheKey = "twitter.latest-posts.{$username}.{$limit}";

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
                        ->get($this->baseUrl.'/timeline.php', [
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

    public function getPostsByHashtag(
        string $username,
        string $hashtag,
        int $limit = 7
    ): Collection {
        $username = ltrim($username, '@');
        $hashtag = '#'.ltrim($hashtag, '#');
        $cacheKey = 'twitter.hashtag-posts.'.md5("{$username}|{$hashtag}|{$limit}");

        $posts = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($username, $hashtag, $limit): array {
                try {
                    $response = Http::timeout(15)
                        ->connectTimeout(5)
                        ->retry(2, 500)
                        ->withHeaders([
                            'X-RapidAPI-Key' => config('services.twitter.key'),
                            'X-RapidAPI-Host' => config('services.twitter.host'),
                        ])
                        ->get($this->baseUrl.'/search.php', [
                            'query' => "from:{$username} {$hashtag}",
                            'search_type' => 'Latest',
                        ]);

                    if ($response->failed()) {
                        Log::warning('Error buscando publicaciones en X', [
                            'username' => $username,
                            'hashtag' => $hashtag,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);

                        return [];
                    }

                    return $this->normalizePosts(
                        $response->json(),
                        $username,
                        $limit
                    )->filter(fn (array $post) => str_contains(
                        mb_strtolower($post['text'] ?? ''),
                        mb_strtolower($hashtag)
                    ))->values()->all();
                } catch (\Throwable $exception) {
                    Log::error('Excepción buscando publicaciones en X', [
                        'username' => $username,
                        'hashtag' => $hashtag,
                        'message' => $exception->getMessage(),
                    ]);

                    return [];
                }
            }
        );

        return collect(is_array($posts) ? $posts : []);
    }

    public function getPostsByHashtagBetween(
        string $username,
        string $hashtag,
        string $since,
        string $until,
        int $maxPages = 10
    ): Collection {
        $username = ltrim($username, '@');
        $hashtag = '#'.ltrim($hashtag, '#');
        $cursor = null;
        $posts = collect();

        for ($page = 0; $page < $maxPages; $page++) {
            $parameters = [
                'query' => "from:{$username} {$hashtag} since:{$since} until:{$until}",
                'search_type' => 'Latest',
            ];

            if ($cursor) {
                $parameters['cursor'] = $cursor;
            }

            try {
                $response = Http::timeout(30)
                    ->connectTimeout(5)
                    ->retry(2, 500)
                    ->withHeaders([
                        'X-RapidAPI-Key' => config('services.twitter.key'),
                        'X-RapidAPI-Host' => config('services.twitter.host'),
                    ])
                    ->get($this->baseUrl.'/search.php', $parameters);

                if ($response->failed()) {
                    Log::warning('Error recuperando publicaciones históricas de X', [
                        'username' => $username,
                        'hashtag' => $hashtag,
                        'status' => $response->status(),
                    ]);
                    break;
                }

                $payload = $response->json();
                $pagePosts = $this->normalizePosts($payload, $username, 20);
                $posts = $posts->concat($pagePosts);
                $nextCursor = $payload['next_cursor'] ?? null;

                if (! $nextCursor || $nextCursor === $cursor || $pagePosts->isEmpty()) {
                    break;
                }

                $cursor = $nextCursor;
            } catch (\Throwable $exception) {
                Log::error('Excepción recuperando publicaciones históricas de X', [
                    'username' => $username,
                    'hashtag' => $hashtag,
                    'message' => $exception->getMessage(),
                ]);
                break;
            }
        }

        $start = Carbon::parse($since)->startOfDay();
        $end = Carbon::parse($until)->startOfDay();

        return $posts
            ->filter(function (array $post) use ($hashtag, $start, $end): bool {
                if (! str_contains(mb_strtolower($post['text'] ?? ''), mb_strtolower($hashtag))) {
                    return false;
                }

                try {
                    $date = Carbon::parse($post['created_at'] ?? null);

                    return $date->greaterThanOrEqualTo($start) && $date->lessThan($end);
                } catch (\Throwable) {
                    return false;
                }
            })
            ->unique('id')
            ->values();
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
