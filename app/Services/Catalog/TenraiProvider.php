<?php

namespace App\Services\Catalog;

use App\Contracts\AnimeCatalogProvider;
use App\Data\CatalogAnime;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TenraiProvider implements AnimeCatalogProvider
{
    public function sourceName(): string
    {
        return 'MyAnimeList / Tenrai';
    }

    public function cacheIdentity(): string
    {
        return 'tenrai:v2:'.rtrim(config('services.anime_catalog.base_url'), '/');
    }

    public function search(string $query): array
    {
        $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
            ->get(rtrim(config('services.anime_catalog.base_url'), '/').'/anime', ['q' => $query, 'limit' => 10]);
        $data = $response->json('data');
        if (! $response->successful() || ! is_array($data) || ! array_is_list($data)) {
            throw new RuntimeException('Invalid catalog response.', $response->status());
        }

        $results = [];
        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }
            $id = filter_var($item['mal_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
            $title = $item['title'] ?? null;
            $image = data_get($item, 'images.jpg.large_image_url') ?: data_get($item, 'images.jpg.image_url');
            if (! $id || ! is_string($title) || trim($title) === '' || ! is_string($image) || ! filter_var($image, FILTER_VALIDATE_URL) || ! in_array(parse_url($image, PHP_URL_SCHEME), ['http', 'https'], true)) {
                continue;
            }
            $episodes = filter_var($item['episodes'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
            $genres = [];
            foreach (['genres', 'themes', 'demographics'] as $group) {
                foreach (is_array($item[$group] ?? null) ? $item[$group] : [] as $tag) {
                    if (is_array($tag) && is_string($tag['name'] ?? null)) {
                        $genres[] = $tag['name'];
                    }
                }
            }
            $results[] = new CatalogAnime(
                $id, mb_substr(trim($title), 0, 255), $image, $episodes ?: null,
                is_string($item['synopsis'] ?? null) ? mb_substr($item['synopsis'], 0, 20000) : null,
                is_string($item['status'] ?? null) ? mb_substr($item['status'], 0, 100) : null,
                array_values(array_unique($genres)),
            );
        }

        return $results;
    }
}
