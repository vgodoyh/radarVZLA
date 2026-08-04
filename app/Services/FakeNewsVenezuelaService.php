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

class FakeNewsVenezuelaService
{
    private const BASE_URL = 'https://fakenewsvenezuela.org';

    private const SECTIONS = [
        'en_profundidad' => [
            'name' => 'En profundidad',
            'url' => 'https://fakenewsvenezuela.org/en-profundidad/',
        ],

        'noti_fake' => [
            'name' => 'Noti-fake',
            'url' => 'https://fakenewsvenezuela.org/noti-fake/',
        ],
    ];

    /**
     * Devuelve las publicaciones separadas por sección.
     */
    public function getLatestPosts(int $limit = 5): array
    {
        return [
            'en_profundidad' => $this->getPostsBySection(
                'en_profundidad',
                $limit
            ),

            'noti_fake' => $this->getPostsBySection(
                'noti_fake',
                $limit
            ),
        ];
    }

    /**
     * Devuelve las publicaciones de una sección.
     */
    public function getPostsBySection(
        string $section,
        int $limit = 5
    ): Collection {
        if (! isset(self::SECTIONS[$section])) {
            Log::warning(
                'Sección de Fake News Venezuela no válida',
                [
                    'section' => $section,
                ]
            );

            return collect();
        }

        $sectionData = self::SECTIONS[$section];

        /*
         * Se guarda un array en caché para evitar problemas
         * de serialización con Collection y Carbon.
         */
        $posts = Cache::remember(
            "fake-news-venezuela-v6.{$section}.latest-posts.{$limit}",
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

        return collect($posts);
    }

    /**
     * Devuelve todas las publicaciones unificadas y ordenadas.
     */
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
                ->timeout(25)
                ->retry(2, 500)
                ->get($url);

            if ($response->failed()) {
                Log::warning(
                    'No se pudo consultar Fake News Venezuela',
                    [
                        'section' => $sectionKey,
                        'url' => $url,
                        'status' => $response->status(),
                    ]
                );

                return collect();
            }

            return $this->parseHtml(
                html: $response->body(),
                sectionUrl: $url,
                sectionKey: $sectionKey,
                sectionName: $sectionName,
                limit: $limit
            );
        } catch (\Throwable $e) {
            Log::error(
                'Error consultando Fake News Venezuela',
                [
                    'section' => $sectionKey,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]
            );

            return collect();
        }
    }

    /**
     * Analiza la página de categoría.
     */
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
         * Buscamos artículos que contengan un título h2 enlazado.
         */
        $articles = $xpath->query(
            '//article[.//h2//a[@href]]'
        );

        $posts = collect();
        $seenUrls = [];

        if (! $articles) {
            return $posts;
        }

        foreach ($articles as $article) {
            if ($posts->count() >= $limit) {
                break;
            }

            if (! $article instanceof DOMElement) {
                continue;
            }

            $titleLink = $xpath->query(
                './/h2//a[@href]',
                $article
            )?->item(0);

            if (! $titleLink instanceof DOMElement) {
                continue;
            }

            $postUrl = $this->absoluteUrl(
                trim($titleLink->getAttribute('href'))
            );

            $title = $this->cleanText(
                $titleLink->textContent
            );

            if (
                $postUrl === '' ||
                $title === '' ||
                mb_strlen($title) < 10
            ) {
                continue;
            }

            if (
                rtrim($postUrl, '/') === rtrim($sectionUrl, '/') ||
                Str::lower($title) === Str::lower($sectionName)
            ) {
                continue;
            }

            if (! Str::startsWith(
                $postUrl,
                self::BASE_URL.'/'
            )) {
                continue;
            }

            if (isset($seenUrls[$postUrl])) {
                continue;
            }

            $seenUrls[$postUrl] = true;

            $contenido = $this->extractExcerpt(
                $xpath,
                $article
            );

            $date = $this->extractDate(
                $xpath,
                $article
            );

            $imagen = $this->extractImage(
                $xpath,
                $article
            );

            /*
             * Si alguno de los datos no está en la categoría,
             * entramos a la página individual.
             */
            $details = [];

            if (
                $contenido === '' ||
                $date === null ||
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

                'fecha' => $date?->toDateTimeString()
                    ?? ($details['fecha'] ?? null),

                'imagen' => $imagen
                    ?? ($details['imagen'] ?? null),

                'seccion' => $sectionKey,
                'seccion_nombre' => $sectionName,
            ]);
        }

        return $posts;
    }

    /**
     * Obtiene contenido, fecha e imagen desde la publicación individual.
     */
    private function getPostDetails(string $url): array
    {
        return Cache::remember(
            'fake-news-post-details-v3.'.md5($url),
            now()->addHours(6),
            function () use ($url): array {
                try {
                    $response = Http::withHeaders(
                        $this->headers()
                    )
                        ->connectTimeout(10)
                        ->timeout(20)
                        ->retry(2, 500)
                        ->get($url);

                    if ($response->failed()) {
                        Log::warning(
                            'No se pudo obtener el detalle de publicación',
                            [
                                'url' => $url,
                                'status' => $response->status(),
                            ]
                        );

                        return [];
                    }

                    return $this->parsePostDetails(
                        $response->body()
                    );
                } catch (\Throwable $e) {
                    Log::warning(
                        'Error obteniendo detalle de publicación',
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

    /**
     * Analiza la página individual de una publicación.
     */
    private function parsePostDetails(string $html): array
    {
        $xpath = $this->createXPath($html);

        if (! $xpath) {
            return [];
        }

        /*
         * Primero intentamos obtener contenido visible.
         */
        $contenido = $this->extractArticleContent(
            $xpath
        );

        /*
         * Fallback con descripción SEO.
         */
        if ($contenido === '') {
            $contenido = $this->extractMetaContent(
                $xpath,
                [
                    '//meta[@property="og:description"]/@content',
                    '//meta[@name="description"]/@content',
                    '//meta[@name="twitter:description"]/@content',
                ]
            );
        }

        $imagen = $this->extractMetaContent(
            $xpath,
            [
                '//meta[@property="og:image"]/@content',
                '//meta[@name="twitter:image"]/@content',
            ]
        );

        $fecha = $this->extractMetaContent(
            $xpath,
            [
                '//meta[@property="article:published_time"]/@content',
                '//meta[@name="date"]/@content',
                '//time/@datetime',
            ]
        );

        $parsedDate = null;

        if ($fecha !== '') {
            try {
                $parsedDate = Carbon::parse(
                    $fecha
                )->toDateTimeString();
            } catch (\Throwable) {
                $parsedDate = null;
            }
        }

        return [
            'contenido' => $contenido,

            'imagen' => $imagen !== ''
                ? $this->absoluteUrl($imagen)
                : null,

            'fecha' => $parsedDate,
        ];
    }

    /**
     * Extrae el resumen desde una tarjeta de la categoría.
     */
    private function extractExcerpt(
        DOMXPath $xpath,
        ?DOMElement $container
    ): string {
        if (! $container) {
            return '';
        }

        $queries = [
            './/*[contains(@class, "excerpt")]',
            './/*[contains(@class, "summary")]',
            './/*[contains(@class, "description")]',
            './/*[contains(@class, "content")]//p',
            './/p',
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

                if ($this->isValidExcerpt($text)) {
                    return $text;
                }
            }
        }

        return '';
    }

    /**
     * Extrae el contenido desde la página individual.
     *
     * Este es el método extractArticleContent al que te referías.
     */
    private function extractArticleContent(
        DOMXPath $xpath
    ): string {
        /*
         * Primero buscamos una entradilla, subtítulo o resumen.
         */
        $summaryQueries = [
            '//*[contains(@class, "entry__excerpt")]',
            '//*[contains(@class, "entry-excerpt")]',
            '//*[contains(@class, "post-excerpt")]',
            '//*[contains(@class, "excerpt")]',
            '//*[contains(@class, "subtitle")]',
            '//*[contains(@class, "sub-title")]',
            '//*[contains(@class, "summary")]',
            '//*[contains(@class, "lead")]',
            '//*[contains(@class, "description")]',
        ];

        foreach ($summaryQueries as $query) {
            $nodes = $xpath->query($query);

            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                $text = $this->cleanText(
                    $node->textContent
                );

                if ($this->isValidExcerpt($text)) {
                    return $text;
                }
            }
        }

        /*
         * Luego buscamos párrafos en el contenido principal.
         */
        $paragraphQueries = [
            '//main//article//*[contains(@class, "entry-content")]//p',
            '//main//article//*[contains(@class, "post-content")]//p',
            '//main//article//*[contains(@class, "content")]//p',
            '//main//article//p',
            '//article//*[contains(@class, "entry-content")]//p',
            '//article//*[contains(@class, "post-content")]//p',
            '//article//*[contains(@class, "content")]//p',
            '//article//p',
            '//*[contains(@class, "entry-content")]//p',
            '//*[contains(@class, "post-content")]//p',
            '//main//p',
        ];

        foreach ($paragraphQueries as $query) {
            $paragraphs = $xpath->query($query);

            if (! $paragraphs) {
                continue;
            }

            foreach ($paragraphs as $paragraph) {
                $text = $this->cleanText(
                    $paragraph->textContent
                );

                if ($this->isValidExcerpt($text)) {
                    return $text;
                }
            }
        }

        return '';
    }

    private function extractDate(
        DOMXPath $xpath,
        ?DOMElement $container
    ): ?Carbon {
        if (! $container) {
            return null;
        }

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

                $parsedDate = $this->parseDate($date);

                if ($parsedDate) {
                    return $parsedDate;
                }
            }
        }

        $dateNodes = $xpath->query(
            './/*[contains(translate(@class, "DATE", "date"), "date")]',
            $container
        );

        if ($dateNodes) {
            foreach ($dateNodes as $dateNode) {
                $date = $this->cleanText(
                    $dateNode->textContent
                );

                $parsedDate = $this->parseDate($date);

                if ($parsedDate) {
                    return $parsedDate;
                }
            }
        }

        $containerText = $this->cleanText(
            $container->textContent
        );

        if (
            preg_match(
                '/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre)\s+\d{1,2},\s+\d{4}\b/ui',
                $containerText,
                $matches
            )
        ) {
            return $this->parseDate(
                $matches[0]
            );
        }

        if (
            preg_match(
                '/\b\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}\b/',
                $containerText,
                $matches
            )
        ) {
            return $this->parseDate(
                $matches[0]
            );
        }

        return null;
    }

    private function parseDate(string $date): ?Carbon
    {
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
            // Continuamos.
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

        $normalizedDate = Str::lower($date);

        foreach ($months as $spanish => $english) {
            $normalizedDate = str_replace(
                $spanish,
                $english,
                $normalizedDate
            );
        }

        try {
            return Carbon::parse(
                $normalizedDate
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractImage(
        DOMXPath $xpath,
        ?DOMElement $container
    ): ?string {
        if (! $container) {
            return null;
        }

        $images = $xpath->query(
            './/img',
            $container
        );

        if ($images) {
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

                    $src = $this->extractFirstSrcsetUrl(
                        $srcset
                    );
                }

                if (
                    $src === '' ||
                    Str::startsWith($src, 'data:image') ||
                    Str::contains(
                        Str::lower($src),
                        [
                            'blank.gif',
                            'transparent.gif',
                            'placeholder',
                        ]
                    )
                ) {
                    continue;
                }

                return $this->absoluteUrl($src);
            }
        }

        /*
         * Fallback para background-image.
         */
        $styledNodes = $xpath->query(
            './/*[@style[contains(., "background-image")]]',
            $container
        );

        if ($styledNodes) {
            foreach ($styledNodes as $styledNode) {
                if (! $styledNode instanceof DOMElement) {
                    continue;
                }

                $style = $styledNode->getAttribute(
                    'style'
                );

                if (
                    preg_match(
                        '/background-image\s*:\s*url\([\'"]?([^\'")]+)[\'"]?\)/i',
                        $style,
                        $matches
                    )
                ) {
                    return $this->absoluteUrl(
                        html_entity_decode(
                            trim($matches[1]),
                            ENT_QUOTES | ENT_HTML5,
                            'UTF-8'
                        )
                    );
                }
            }
        }

        return null;
    }

    private function extractFirstSrcsetUrl(
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

        /*
         * Normalmente la última es la imagen de mayor resolución.
         */
        $source = end($sources);

        return trim(
            preg_split(
                '/\s+/',
                $source
            )[0] ?? ''
        );
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

    private function isValidExcerpt(string $text): bool
    {
        if (mb_strlen($text) < 40) {
            return false;
        }

        $ignoredTexts = [
            'suscríbete',
            'subscribete',
            'compartir',
            'síguenos',
            'todos los derechos reservados',
            'designed & developed',
            'política de privacidad',
            'sin comentarios',
            'minutos de lectura',
            'leave a comment',
            'related posts',
        ];

        $normalizedText = Str::lower($text);

        foreach ($ignoredTexts as $ignoredText) {
            if (
                Str::contains(
                    $normalizedText,
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

        $dom = new DOMDocument;

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">'.$html,
            LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        return new DOMXPath($dom);
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);

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
            return 'https:'.$url;
        }

        return self::BASE_URL.'/'.ltrim(
            $url,
            '/'
        );
    }

    private function cleanText(?string $text): string
    {
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
                .'AppleWebKit/537.36 (KHTML, like Gecko) '
                .'Chrome/150.0.0.0 Safari/537.36',

            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',

            'Accept-Language' => 'es-ES,es;q=0.9',
        ];
    }
}
