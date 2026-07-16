<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccesoJusticiaService
{
    private const BASE_URL = 'https://accesoalajusticia.org';

    private const SECTIONS = [
        'prensa' => [
            'name' => 'Prensa',
            'url' => 'https://accesoalajusticia.org/prensa/',
        ],

        'persecucion_politica' => [
            'name' => 'Persecución Política',
            'url' => 'https://accesoalajusticia.org/persecucion-politica/',
        ],
    ];

    public function getLatestPosts(int $limit = 5): array
    {
        return [
            'prensa' => $this->getPostsBySection(
                'prensa',
                $limit
            ),

            'persecucion_politica' => $this->getPostsBySection(
                'persecucion_politica',
                $limit
            ),
        ];
    }

    public function getPostsBySection(
        string $section,
        int $limit = 5
    ): Collection {
        if (! isset(self::SECTIONS[$section])) {
            Log::warning(
                'Sección de Acceso a la Justicia no válida',
                [
                    'section' => $section,
                ]
            );

            return collect();
        }

        $sectionData = self::SECTIONS[$section];

        /*
         * Versión nueva para no reutilizar los resultados
         * vacíos guardados anteriormente.
         */
        $posts = Cache::remember(
            "acceso-justicia-v10.{$section}.{$limit}",
            now()->addMinutes(30),
            function () use (
                $sectionData,
                $section,
                $limit
            ): array {
                return $this->scrape(
                    url: $sectionData['url'],
                    sectionKey: $section,
                    sectionName: $sectionData['name'],
                    limit: $limit
                )->toArray();
            }
        );

        return collect(
            is_array($posts) ? $posts : []
        );
    }

    public function getCombinedLatestPosts(
        int $limitPerSection = 5
    ): Collection {
        return collect(
            $this->getLatestPosts($limitPerSection)
        )
            ->flatten(1)
            ->sortByDesc(
                fn (array $post) => $post['fecha'] ?? ''
            )
            ->values();
    }

    private function scrape(
        string $url,
        string $sectionKey,
        string $sectionName,
        int $limit
    ): Collection {
        try {
            $response = Http::withHeaders(
                $this->headers()
            )
                ->connectTimeout(10)
                ->timeout(30)
                ->retry(2, 500)
                ->get($url);

            if ($response->failed()) {
                Log::warning(
                    'Respuesta no válida de Acceso a la Justicia',
                    [
                        'section' => $sectionKey,
                        'url' => $url,
                        'status' => $response->status(),
                    ]
                );

                return collect();
            }

            $html = $response->body();

            Log::debug(
                'HTML recibido de Acceso a la Justicia',
                [
                    'section' => $sectionKey,
                    'status' => $response->status(),
                    'length' => strlen($html),
                ]
            );

            if (trim($html) === '') {
                return collect();
            }

            return $this->parseHtml(
                html: $html,
                sectionUrl: $url,
                sectionKey: $sectionKey,
                sectionName: $sectionName,
                limit: $limit
            );
        } catch (\Throwable $e) {
            Log::error(
                'Error consultando Acceso a la Justicia',
                [
                    'section' => $sectionKey,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]
            );

            return collect();
        }
    }

    private function parseHtml(
        string $html,
        string $sectionUrl,
        string $sectionKey,
        string $sectionName,
        int $limit
    ): Collection {
        $xpath = $this->createXPath($html);

        if (! $xpath) {
            return collect();
        }

        /*
         * No limitamos la búsqueda a h1, h2, h3 o h4.
         *
         * Buscamos todos los enlaces internos y después
         * determinamos cuáles corresponden a publicaciones.
         */
        $links = $xpath->query(
            '//a[@href and normalize-space(string(.)) != ""]'
        );

        if (! $links) {
            return collect();
        }

        $posts = collect();
        $seenUrls = [];

        foreach ($links as $link) {
            if ($posts->count() >= $limit) {
                break;
            }

            if (! $link instanceof DOMElement) {
                continue;
            }

            $title = $this->cleanText(
                $link->textContent
            );

            $postUrl = $this->absoluteUrl(
                trim($link->getAttribute('href'))
            );

            if (! $this->isValidCandidate(
                title: $title,
                url: $postUrl,
                sectionUrl: $sectionUrl
            )) {
                continue;
            }

            if (isset($seenUrls[$postUrl])) {
                continue;
            }

            /*
             * Buscamos el contenedor mínimo que incluya
             * título, resumen, fecha e imagen.
             */
            $container = $this->findPostContainer(
                link: $link,
                xpath: $xpath,
                title: $title
            );

            if (! $container) {
                continue;
            }

            $fecha = $this->extractDate(
                $xpath,
                $container
            );

            /*
             * Toda tarjeta real del listado tiene fecha.
             * Esto evita capturar enlaces del menú o footer.
             */
            if (! $fecha) {
                continue;
            }

            $seenUrls[$postUrl] = true;

            $contenido = $this->extractExcerpt(
                xpath: $xpath,
                container: $container,
                title: $title
            );

            $imagen = $this->extractImage(
                $xpath,
                $container
            );

            /*
             * Fallback a la página individual si falta
             * contenido o imagen.
             */
            $details = [];

            if (
                $contenido === '' ||
                $imagen === null
            ) {
                $details = $this->getPostDetails(
                    $postUrl
                );
            }

            $posts->push([
                'titulo' => $title,

                'url' => $postUrl,

                'contenido' => $contenido !== ''
                    ? $contenido
                    : ($details['contenido'] ?? ''),

                'fecha' => $fecha->toDateTimeString(),

                'imagen' => $imagen
                    ?? ($details['imagen'] ?? null),

                'seccion' => $sectionKey,

                'seccion_nombre' => $sectionName,
            ]);
        }

        return $posts;
    }

    private function isValidCandidate(
        string $title,
        string $url,
        string $sectionUrl
    ): bool {
        if (
            $title === '' ||
            mb_strlen($title) < 15 ||
            mb_strlen($title) > 250
        ) {
            return false;
        }

        if ($url === '') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (
            ! in_array(
                $host,
                [
                    'accesoalajusticia.org',
                    'www.accesoalajusticia.org',
                ],
                true
            )
        ) {
            return false;
        }

        if (
            rtrim($url, '/') ===
            rtrim($sectionUrl, '/')
        ) {
            return false;
        }

        $path = trim(
            parse_url($url, PHP_URL_PATH) ?? '',
            '/'
        );

        if ($path === '') {
            return false;
        }

        /*
         * Las publicaciones tienen normalmente un slug
         * único en la raíz del dominio.
         */
        if (str_contains($path, '/')) {
            return false;
        }

        $ignoredPaths = [
            'prensa',
            'persecucion-politica',
            'nosotros',
            'contacto',
            'el-rincon-de-ali',
            'el-rincon-de-laura',
        ];

        if (in_array($path, $ignoredPaths, true)) {
            return false;
        }

        $normalizedTitle = Str::lower(
            trim($title)
        );

        $ignoredTitles = [
            'leer más',
            'prensa',
            'persecución política',
            'persecucion politica',
            'destacado',
            'cronología',
            'artículos',
            'análisis',
            'quiénes somos',
            'publicaciones',
            'recientes',
            'infografías',
            'facebook-f',
            'x-twitter',
            'instagram',
            'youtube',
            'tiktok',
            'telegram',
        ];

        return ! in_array(
            $normalizedTitle,
            $ignoredTitles,
            true
        );
    }

    private function findPostContainer(
        DOMElement $link,
        DOMXPath $xpath,
        string $title
    ): ?DOMElement {
        $current = $link;

        /*
         * Buscamos el ancestro más pequeño que contenga
         * una fecha y el título de la publicación.
         */
        for ($level = 0; $level < 15; $level++) {
            $parent = $current->parentNode;

            if (! $parent instanceof DOMElement) {
                break;
            }

            $current = $parent;

            $text = $this->cleanText(
                $current->textContent
            );

            if (! Str::contains($text, $title)) {
                continue;
            }

            /*
             * Evitar tomar contenedores gigantes como body,
             * main o toda la cuadrícula.
             */
            if (mb_strlen($text) > 3000) {
                continue;
            }

            if ($this->textContainsDate($text)) {
                return $current;
            }

            $hasTime = (
                $xpath->query(
                    './/time',
                    $current
                )?->length ?? 0
            ) > 0;

            if ($hasTime) {
                return $current;
            }
        }

        return null;
    }

    private function extractExcerpt(
        DOMXPath $xpath,
        DOMElement $container,
        string $title
    ): string {
        $queries = [
            './/*[contains(@class, "elementor-post__excerpt")]',
            './/*[contains(@class, "entry-summary")]',
            './/*[contains(@class, "post-excerpt")]',
            './/*[contains(@class, "excerpt")]',
            './/*[contains(@class, "description")]',
            './/p[normalize-space(string(.)) != ""]',
        ];

        foreach ($queries as $query) {
            $nodes = $xpath->query(
                $query,
                $container
            );

            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                $text = $this->cleanText(
                    $node->textContent
                );

                $text = preg_replace(
                    '/\s*leer más\s*$/iu',
                    '',
                    $text
                ) ?? $text;

                if (
                    $this->isValidExcerpt(
                        $text,
                        $title
                    )
                ) {
                    return $text;
                }
            }
        }

        /*
         * Fallback: obtenemos el texto completo de la tarjeta
         * y eliminamos título, categorías, fecha y "Leer más".
         */
        $text = $this->cleanText(
            $container->textContent
        );

        $text = str_replace(
            $title,
            '',
            $text
        );

        $text = preg_replace(
            '/\b(destacado|prensa|persecución política|persecucion politica|cronología|cronologia|artículos|articulos|análisis|analisis)\b/iu',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\s+\d{1,2},\s+\d{4}\b/iu',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\bleer más\b/iu',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\s+\d+\s*$/u',
            '',
            $text
        ) ?? $text;

        $text = $this->cleanText($text);

        return $this->isValidExcerpt(
            $text,
            $title
        )
            ? $text
            : '';
    }

    private function extractDate(
        DOMXPath $xpath,
        DOMElement $container
    ): ?Carbon {
        $timeNodes = $xpath->query(
            './/time',
            $container
        );

        if ($timeNodes) {
            foreach ($timeNodes as $timeNode) {
                if (! $timeNode instanceof DOMElement) {
                    continue;
                }

                $date = trim(
                    $timeNode->getAttribute('datetime')
                );

                if ($date === '') {
                    $date = $this->cleanText(
                        $timeNode->textContent
                    );
                }

                $parsed = $this->parseDate($date);

                if ($parsed) {
                    return $parsed;
                }
            }
        }

        $text = $this->cleanText(
            $container->textContent
        );

        if (
            preg_match(
                '/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\s+\d{1,2},\s+\d{4}\b/ui',
                $text,
                $matches
            )
        ) {
            return $this->parseDate(
                $matches[0]
            );
        }

        if (
            preg_match(
                '/\b\d{1,2}\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\s+de\s+\d{4}\b/ui',
                $text,
                $matches
            )
        ) {
            return $this->parseDate(
                $matches[0]
            );
        }

        return null;
    }

    private function textContainsDate(
        string $text
    ): bool {
        return preg_match(
            '/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\s+\d{1,2},\s+\d{4}\b/ui',
            $text
        ) === 1;
    }

    private function parseDate(
        string $date
    ): ?Carbon {
        $date = $this->cleanText($date);

        if ($date === '') {
            return null;
        }

        try {
            if (
                preg_match(
                    '/^\d{4}-\d{2}-\d{2}/',
                    $date
                )
            ) {
                return Carbon::parse($date);
            }
        } catch (\Throwable) {
            // Continuamos con formato en español.
        }

        $months = [
            'enero' => 'January',
            'febrero' => 'February',
            'marzo' => 'March',
            'abril' => 'April',
            'mayo' => 'May',
            'junio' => 'June',
            'julio' => 'July',
            'agosto' => 'August',
            'septiembre' => 'September',
            'setiembre' => 'September',
            'octubre' => 'October',
            'noviembre' => 'November',
            'diciembre' => 'December',
        ];

        $normalized = Str::lower($date);

        $normalized = preg_replace(
            '/\bde\b/ui',
            ' ',
            $normalized
        ) ?? $normalized;

        foreach ($months as $spanish => $english) {
            $normalized = str_replace(
                $spanish,
                $english,
                $normalized
            );
        }

        try {
            return Carbon::parse($normalized);
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractImage(
        DOMXPath $xpath,
        DOMElement $container
    ): ?string {
        $images = $xpath->query(
            './/img',
            $container
        );

        if (! $images) {
            return null;
        }

        foreach ($images as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }

            $src = trim(
                $image->getAttribute('data-lazy-src')
                ?: $image->getAttribute('data-src')
                ?: $image->getAttribute('src')
            );

            if ($src === '') {
                $srcset = trim(
                    $image->getAttribute('data-srcset')
                    ?: $image->getAttribute('srcset')
                );

                $src = $this->extractLastSrcsetUrl(
                    $srcset
                );
            }

            if (
                $src === '' ||
                Str::startsWith($src, 'data:image') ||
                Str::contains(
                    Str::lower($src),
                    [
                        'placeholder',
                        'transparent',
                        'blank.gif',
                        'logo',
                        'avatar',
                    ]
                )
            ) {
                continue;
            }

            return $this->absoluteUrl($src);
        }

        return null;
    }

    private function getPostDetails(
        string $url
    ): array {
        return Cache::remember(
            'acceso-justicia-detail-v10.' . md5($url),
            now()->addHours(6),
            function () use ($url): array {
                try {
                    $response = Http::withHeaders(
                        $this->headers()
                    )
                        ->connectTimeout(10)
                        ->timeout(25)
                        ->retry(2, 500)
                        ->get($url);

                    if ($response->failed()) {
                        return [];
                    }

                    return $this->parsePostDetails(
                        $response->body()
                    );
                } catch (\Throwable $e) {
                    Log::warning(
                        'Error leyendo detalle de Acceso a la Justicia',
                        [
                            'url' => $url,
                            'error' => $e->getMessage(),
                        ]
                    );

                    return [];
                }
            }
        );
    }

    private function parsePostDetails(
        string $html
    ): array {
        $xpath = $this->createXPath($html);

        if (! $xpath) {
            return [];
        }

        $contenido = $this->extractMetaContent(
            $xpath,
            [
                '//meta[@property="og:description"]/@content',
                '//meta[@name="description"]/@content',
                '//meta[@name="twitter:description"]/@content',
            ]
        );

        if ($contenido === '') {
            $queries = [
                '//article//*[contains(@class, "entry-content")]//p',
                '//*[contains(@class, "elementor-widget-theme-post-content")]//p',
                '//main//article//p',
                '//article//p',
            ];

            foreach ($queries as $query) {
                $nodes = $xpath->query($query);

                if (! $nodes) {
                    continue;
                }

                foreach ($nodes as $node) {
                    $text = $this->cleanText(
                        $node->textContent
                    );

                    if (
                        mb_strlen($text) >= 40
                    ) {
                        $contenido = $text;
                        break 2;
                    }
                }
            }
        }

        $imagen = $this->extractMetaContent(
            $xpath,
            [
                '//meta[@property="og:image"]/@content',
                '//meta[@name="twitter:image"]/@content',
            ]
        );

        return [
            'contenido' => $contenido,

            'imagen' => $imagen !== ''
                ? $this->absoluteUrl($imagen)
                : null,
        ];
    }

    private function extractMetaContent(
        DOMXPath $xpath,
        array $queries
    ): string {
        foreach ($queries as $query) {
            $node = $xpath->query(
                $query
            )?->item(0);

            if (! $node) {
                continue;
            }

            $value = $this->cleanText(
                $node->nodeValue
            );

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractLastSrcsetUrl(
        string $srcset
    ): string {
        if ($srcset === '') {
            return '';
        }

        $sources = array_filter(
            array_map(
                'trim',
                explode(',', $srcset)
            )
        );

        if ($sources === []) {
            return '';
        }

        $source = end($sources);

        return trim(
            preg_split(
                '/\s+/',
                $source
            )[0] ?? ''
        );
    }

    private function isValidExcerpt(
        string $text,
        string $title = ''
    ): bool {
        if (
            $text === '' ||
            mb_strlen($text) < 40
        ) {
            return false;
        }

        if (
            $title !== '' &&
            trim($text) === trim($title)
        ) {
            return false;
        }

        $normalized = Str::lower($text);

        $ignored = [
            'todos los derechos reservados',
            'política de privacidad',
            'quiénes somos',
            'a nuestros boletines',
            'gracias por suscribirte',
            'nombre de usuario',
            'correo electrónico',
        ];

        foreach ($ignored as $ignoredText) {
            if (
                Str::contains(
                    $normalized,
                    $ignoredText
                )
            ) {
                return false;
            }
        }

        return true;
    }

    private function createXPath(
        string $html
    ): ?DOMXPath {
        if (trim($html) === '') {
            return null;
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NOERROR |
            LIBXML_NOWARNING |
            LIBXML_NONET
        );

        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        return new DOMXPath($dom);
    }

    private function absoluteUrl(
        string $url
    ): string {
        $url = trim(
            html_entity_decode(
                $url,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );

        if ($url === '') {
            return '';
        }

        if (
            preg_match(
                '#^https?://#i',
                $url
            )
        ) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            return 'https:' . $url;
        }

        return self::BASE_URL . '/' . ltrim(
            $url,
            '/'
        );
    }

    private function cleanText(
        ?string $text
    ): string {
        $text = html_entity_decode(
            strip_tags($text ?? ''),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim(
            preg_replace(
                '/\s+/u',
                ' ',
                $text
            ) ?? ''
        );
    }

    private function headers(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                . 'Chrome/150.0.0.0 Safari/537.36',

            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',

            'Accept-Language' => 'es-ES,es;q=0.9',

            'Cache-Control' => 'no-cache',

            'Referer' => self::BASE_URL . '/',
        ];
    }
}