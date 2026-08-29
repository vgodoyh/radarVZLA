<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_dashboard_is_available_without_external_services(): void
    {
        $this->get(route('dashboard.public'))->assertOk()->assertSee('Pulso Venezuela');
    }

    public function test_supported_locale_can_be_changed(): void
    {
        $this->from('/')->get(route('language.switch', 'en'))->assertRedirect('/')->assertSessionHas('locale', 'en');
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->get(route('language.switch', 'fr'))->assertNotFound();
    }

    public function test_each_organization_has_an_independent_public_page(): void
    {
        foreach ([
            'organizations.jep',
            'organizations.acceso-justicia',
            'organizations.fake-news',
            'organizations.universidades',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_public_organization_urls_use_their_final_paths(): void
    {
        foreach ([
            '/fake-news',
            '/justicia-encuentro-perdon',
            '/acceso-justicia',
            '/observatorio-universidades',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_legacy_organization_urls_are_not_registered(): void
    {
        foreach ([
            '/organizaciones/fake-news',
            '/organizaciones/jep',
            '/organizaciones/acceso-justicia',
            '/organizaciones/obu',
        ] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_acceso_justicia_page_has_a_database_search(): void
    {
        $this->get(route('organizations.acceso-justicia', ['q' => 'justicia']))
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('value="justicia"', false);
    }

    public function test_acceso_justicia_controls_are_translated(): void
    {
        $this->withSession(['locale' => 'es'])
            ->get(route('organizations.acceso-justicia'))
            ->assertOk()
            ->assertSee('publicaciones')
            ->assertSee('Buscar en publicaciones de #AlertaLegal')
            ->assertSee('Pendiente de sincronización');

        $this->withSession(['locale' => 'en'])
            ->get(route('organizations.acceso-justicia'))
            ->assertOk()
            ->assertSee('publications')
            ->assertSee('Search #AlertaLegal publications');

        $this->assertSame('&laquo; Anterior', __('pagination.previous', locale: 'es'));
        $this->assertSame('Next &raquo;', __('pagination.next', locale: 'en'));
    }
}
