<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsContentClick;
use App\Models\AnalyticsNavigationClick;
use App\Models\AnalyticsPageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AnalyticsTracker
{
    public const VISIT_WINDOW_MINUTES = 30;

    public function recordPageView(Request $request, string $organization, string $page): ?AnalyticsPageView
    {
        if (! Schema::hasTable('analytics_page_views')) {
            return null;
        }

        $organization = $this->normalizeToken($organization, 'organization');
        $page = $this->normalizePath($page, 'page');
        $source = $this->pageViewSource($request, $organization, $page);
        $sessionHash = $this->sessionHash($request);
        $ipHash = $this->ipHash($request);

        $identityColumn = $sessionHash ? 'session_id' : 'ip_hash';
        $identityHash = $sessionHash ?: $ipHash;

        if ($identityHash && AnalyticsPageView::query()
            ->where('organization', $organization)
            ->where('page', $page)
            ->where($identityColumn, $identityHash)
            ->where('source', $source)
            ->where('created_at', '>=', now()->subMinutes(self::VISIT_WINDOW_MINUTES))
            ->exists()) {
            return null;
        }

        return AnalyticsPageView::create([
            'organization' => $organization,
            'page' => $page,
            'source' => $source,
            'session_id' => $sessionHash,
            'ip_hash' => $ipHash,
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function recordContentClick(
        Request $request,
        string $organization,
        string $contentType,
        int $contentId,
        string $source,
    ): AnalyticsContentClick {
        return AnalyticsContentClick::create([
            'organization' => $this->normalizeToken($organization, 'organization'),
            'content_type' => $this->normalizeToken($contentType, 'content type'),
            'content_id' => $contentId,
            'source' => $this->normalizeToken($source, 'source'),
            'session_id' => $this->sessionHash($request),
        ]);
    }

    public function recordNavigationClick(
        Request $request,
        string $organization,
        string $target,
        string $source,
    ): AnalyticsNavigationClick {
        return AnalyticsNavigationClick::create([
            'organization' => $this->normalizeToken($organization, 'organization'),
            'target' => $this->normalizePath($target, 'target'),
            'source' => $this->normalizeToken($source, 'source'),
            'session_id' => $this->sessionHash($request),
        ]);
    }

    private function sessionHash(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $identifier = $request->session()->get('analytics_visitor_id');

        if (blank($identifier)) {
            $identifier = Str::random(40);
            $request->session()->put('analytics_visitor_id', $identifier);
        }

        return $this->hmac((string) $identifier);
    }

    private function ipHash(Request $request): ?string
    {
        return filled($request->ip()) ? $this->hmac((string) $request->ip()) : null;
    }

    private function pageViewSource(Request $request, string $organization, string $page): ?string
    {
        if (! in_array([$organization, $page], [
            ['acceso_justicia', 'acceso-justicia'],
            ['ovfn', 'fake-news'],
        ], true)) {
            return null;
        }

        $explicitSource = null;
        $explicitSourceExpiresAt = 0;

        if ($request->hasSession()) {
            $explicitSource = $request->session()->pull('analytics_page_view_source');
            $explicitSourceExpiresAt = (int) $request->session()->pull('analytics_page_view_source_expires_at', 0);
        }

        if ($explicitSource === 'home' && $explicitSourceExpiresAt >= now()->timestamp) {
            return 'home';
        }

        $referer = trim((string) $request->headers->get('referer'));

        if ($referer === '') {
            return 'direct';
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);
        $refererPath = parse_url($referer, PHP_URL_PATH);

        if (! is_string($refererHost) || $refererHost === '') {
            return 'direct';
        }

        $applicationHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $knownHosts = array_filter([
            strtolower($request->getHost()),
            is_string($applicationHost) ? strtolower($applicationHost) : null,
        ]);
        $normalizedPath = is_string($refererPath) && $refererPath !== '' ? rtrim($refererPath, '/') : '/';

        if (in_array(strtolower($refererHost), $knownHosts, true)
            && in_array($normalizedPath, ['/', '/home'], true)) {
            return 'home';
        }

        return 'external';
    }

    private function hmac(string $value): string
    {
        $key = hash_hmac('sha256', 'pulso-vzla-analytics', (string) config('app.key'), true);

        return hash_hmac('sha256', $value, $key);
    }

    private function normalizeToken(string $value, string $field): string
    {
        $normalized = str($value)
            ->lower()
            ->trim()
            ->replace(['-', ' '], '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();

        if ($normalized === '') {
            throw new InvalidArgumentException("Invalid analytics {$field}.");
        }

        return $normalized;
    }

    private function normalizePath(string $value, string $field): string
    {
        $path = parse_url(trim($value), PHP_URL_PATH);
        $normalized = str(is_string($path) ? $path : '')
            ->lower()
            ->trim('/')
            ->replaceMatches('#/+#', '/')
            ->replaceMatches('/[^a-z0-9_\/-]/', '')
            ->toString();

        if ($normalized === '') {
            throw new InvalidArgumentException("Invalid analytics {$field}.");
        }

        return $normalized;
    }
}
