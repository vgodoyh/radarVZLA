<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_exposes_the_canonical_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent: *')
            ->assertSee('Allow: /')
            ->assertSee('Disallow: /admin/')
            ->assertSee('Disallow: /login')
            ->assertSee('Disallow: /livewire/')
            ->assertSee('Sitemap: https://pulsovenezuela.org/sitemap.xml');
    }

    public function test_sitemap_is_valid_xml_with_only_canonical_public_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml);
        $locations = [];
        foreach ($xml->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertSame([
            'https://pulsovenezuela.org/',
            'https://pulsovenezuela.org/justicia-encuentro-perdon',
            'https://pulsovenezuela.org/acceso-justicia',
            'https://pulsovenezuela.org/fake-news',
            'https://pulsovenezuela.org/observatorio-universidades',
        ], $locations);

        foreach ($locations as $location) {
            $this->assertStringStartsWith('https://pulsovenezuela.org', $location);
            $this->assertStringNotContainsString('www.', $location);
            $this->assertStringNotContainsString('?', $location);
            $this->assertStringNotContainsString('/admin', $location);
            $this->assertStringNotContainsString('/login', $location);
            $this->assertStringNotContainsString('/logout', $location);
            $this->assertStringNotContainsString('/analytics', $location);
            $this->assertStringNotContainsString('/livewire', $location);
        }
    }

    public function test_legacy_navigation_get_redirects_permanently_to_the_canonical_public_page(): void
    {
        $this->get('/analytics/navigation/universidades/home')
            ->assertRedirect('https://pulsovenezuela.org/observatorio-universidades')
            ->assertStatus(301);
    }
}
