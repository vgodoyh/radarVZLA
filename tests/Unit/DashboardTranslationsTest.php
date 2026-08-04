<?php

namespace Tests\Unit;

use Tests\TestCase;

class DashboardTranslationsTest extends TestCase
{
    public function test_spanish_and_english_have_the_same_keys(): void
    {
        $this->assertSame($this->keys(trans('dashboard', [], 'es')), $this->keys(trans('dashboard', [], 'en')));
    }

    private function keys(array $values, string $prefix = ''): array
    {
        $keys = [];
        foreach ($values as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
            if (is_array($value) && ! array_is_list($value)) {
                $keys = [...$keys, ...$this->keys($value, $path)];
            } else {
                $keys[] = $path;
            }
        }
        sort($keys);

        return $keys;
    }
}
