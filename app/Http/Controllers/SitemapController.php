<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            'https://pulsovenezuela.org/',
            'https://pulsovenezuela.org/justicia-encuentro-perdon',
            'https://pulsovenezuela.org/acceso-justicia',
            'https://pulsovenezuela.org/fake-news',
            'https://pulsovenezuela.org/observatorio-universidades',
        ];

        $entries = collect($urls)
            ->map(fn (string $url): string => '    <url><loc>'.e($url).'</loc></url>')
            ->implode("\n");

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            .$entries."\n</urlset>\n";

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
