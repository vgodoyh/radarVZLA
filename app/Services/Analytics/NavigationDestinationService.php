<?php

namespace App\Services\Analytics;

class NavigationDestinationService
{
    /** @var array<string, array{organization: string, target: string, route: string}> */
    private const DESTINATIONS = [
        'jep' => [
            'organization' => 'jep',
            'target' => 'justicia-encuentro-perdon',
            'route' => 'organizations.jep',
        ],
        'acceso-justicia' => [
            'organization' => 'acceso_justicia',
            'target' => 'acceso-justicia',
            'route' => 'organizations.acceso-justicia',
        ],
        'ovfn' => [
            'organization' => 'ovfn',
            'target' => 'fake-news',
            'route' => 'organizations.fake-news',
        ],
        'universidades' => [
            'organization' => 'universidades',
            'target' => 'observatorio-universidades',
            'route' => 'organizations.universidades',
        ],
    ];

    /** @return array{organization: string, target: string, route: string}|null */
    public function resolve(string $organization): ?array
    {
        return self::DESTINATIONS[$organization] ?? null;
    }
}
