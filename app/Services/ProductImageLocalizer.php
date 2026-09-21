<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * A külső forrásból hotlinkelt termékképeket egyszer letölti és a saját
 * tárhelyre teszi, majd a termékek mezőit a helyi útvonalra írja át.
 *
 * Ugyanaz az URL több ezer terméken is szerepelhet, ezért URL szerint
 * csoportosít: minden képet pontosan egyszer tölt le. A fájlnév a tartalom
 * hash-e, így két különböző URL azonos képe is egyetlen fájl lesz.
 */
final class ProductImageLocalizer
{
    private const string DISK = 'public';

    private const string DIRECTORY = 'products';

    /**
     * Content-type => kiterjesztés. Ismeretlen típusnál az URL végződése dönt.
     *
     * @var array<string, string>
     */
    private const array EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/avif' => 'avif',
    ];

    /**
     * @return array{urls: int, downloaded: int, failed: int, products: int, failed_urls: array<int, string>}
     */
    public function localize(bool $dryRun = false): array
    {
        $urls = $this->collectRemoteUrls();

        $stats = ['urls' => count($urls), 'downloaded' => 0, 'failed' => 0, 'products' => 0, 'failed_urls' => []];

        foreach ($urls as $url) {
            if ($dryRun) {
                $stats['products'] += $this->countProductsUsing($url);

                continue;
            }

            $path = $this->store($url);

            if ($path === null) {
                $stats['failed']++;
                $stats['failed_urls'][] = $url;

                continue;
            }

            $stats['downloaded']++;
            $stats['products'] += $this->replaceUrl($url, $path);
        }

        return $stats;
    }

    /**
     * Minden külső kép-URL a featured_image és az images mezőkből.
     *
     * @return array<int, string>
     */
    private function collectRemoteUrls(): array
    {
        $urls = [];

        Product::query()
            ->where(fn ($query) => $query->whereNotNull('featured_image')->orWhereNotNull('images'))
            ->select(['id', 'featured_image', 'images'])
            ->chunkById(500, function ($products) use (&$urls): void {
                foreach ($products as $product) {
                    foreach ([$product->featured_image, ...($product->images ?? [])] as $candidate) {
                        if (is_string($candidate) && $this->isRemote($candidate)) {
                            $urls[$candidate] = true;
                        }
                    }
                }
            });

        return array_keys($urls);
    }

    private function isRemote(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    /**
     * Letölti és elmenti a képet, visszaadva a lemezen lévő útvonalat. Ha a
     * letöltés nem sikerül, null-t ad vissza, és az eredeti URL marad érvényben.
     */
    private function store(string $url): ?string
    {
        try {
            $response = Http::timeout(20)->retry(2, 500, throw: false)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();

        if ($body === '') {
            return null;
        }

        $path = self::DIRECTORY . '/' . hash('sha256', $body) . '.' . $this->extensionFor($url, $response->header('Content-Type'));

        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($path)) {
            $disk->put($path, $body);
        }

        return $path;
    }

    private function extensionFor(string $url, ?string $contentType): string
    {
        $type = mb_strtolower(mb_trim(explode(';', (string) $contentType)[0]));

        if (isset(self::EXTENSIONS[$type])) {
            return self::EXTENSIONS[$type];
        }

        $fromUrl = mb_strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($fromUrl, self::EXTENSIONS, true) ? $fromUrl : 'jpg';
    }

    private function countProductsUsing(string $url): int
    {
        return Product::query()
            ->where('featured_image', $url)
            ->orWhereJsonContains('images', $url)
            ->count();
    }

    /**
     * Átírja az URL-t a helyi útvonalra minden érintett terméken.
     */
    private function replaceUrl(string $url, string $path): int
    {
        $touched = Product::query()->where('featured_image', $url)->update(['featured_image' => $path]);

        Product::query()
            ->whereJsonContains('images', $url)
            ->select(['id', 'images'])
            ->chunkById(500, function ($products) use ($url, $path, &$touched): void {
                foreach ($products as $product) {
                    $images = array_map(
                        fn ($image): mixed => $image === $url ? $path : $image,
                        $product->images ?? [],
                    );

                    Product::query()->whereKey($product->id)->update(['images' => $images]);
                    $touched++;
                }
            });

        return $touched;
    }
}
