<?php

namespace App\Services\Catalog;

use App\Contracts\AnimeCatalogProvider;
use App\Data\CatalogAnime;
use Illuminate\Http\Client\Response;
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
        // Bump when CatalogAnime changes: cached instances of the old shape must not be read back.
        return 'tenrai:v3:'.rtrim(config('services.anime_catalog.base_url'), '/');
    }

    public function search(string $query): array
    {
        $response = $this->get('/anime', ['q' => $query, 'limit' => 10]);
        $data = $response->json('data');
        if (! $response->successful() || ! is_array($data) || ! array_is_list($data)) {
            throw new RuntimeException('Invalid catalog response.', $response->status());
        }

        $results = [];
        foreach ($data as $item) {
            if (is_array($item) && ($anime = $this->map($item))) {
                $results[] = $anime;
            }
        }

        return $results;
    }

    public function find(int $malId): ?CatalogAnime
    {
        // The basic endpoint carries every stored field; /full is about 5x larger and bypasses the API cache.
        $response = $this->get('/anime/'.$malId);
        if ($response->status() === 404) {
            return null;
        }
        $data = $response->json('data');
        if (! $response->successful() || ! is_array($data)) {
            throw new RuntimeException('Invalid catalog response.', $response->status());
        }

        return $this->map($data) ?? throw new RuntimeException('Invalid catalog entry.', 422);
    }

    private function get(string $path, array $query = []): Response
    {
        $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
            ->get(rtrim(config('services.anime_catalog.base_url'), '/').$path, $query);
        if ($response->status() === 429) {
            throw new CatalogRateLimited($this->retryAfter($response));
        }

        return $response;
    }

    private function retryAfter(Response $response): int
    {
        $header = $response->header('Retry-After');
        $seconds = is_numeric($header) ? (int) $header : (($date = strtotime($header)) ? $date - time() : 60);

        return min(3600, max(1, $seconds));
    }

    private function map(array $item): ?CatalogAnime
    {
        $id = filter_var($item['mal_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
        $title = $item['title'] ?? null;
        $image = data_get($item, 'images.jpg.large_image_url') ?: data_get($item, 'images.jpg.image_url');
        if (! $id || ! is_string($title) || trim($title) === '' || ! is_string($image) || ! filter_var($image, FILTER_VALIDATE_URL) || ! in_array(parse_url($image, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return null;
        }
        $title = mb_substr(trim($title), 0, 255);
        $episodes = filter_var($item['episodes'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
        $year = filter_var($item['year'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2100]]);
        [$airedFrom, $airedPrecision] = $this->airedStart($item);
        $season = $this->text($item['season'] ?? null, 10);

        return new CatalogAnime(
            $id, $title, $image, $episodes ?: null,
            is_string($item['synopsis'] ?? null) ? mb_substr($item['synopsis'], 0, 20000) : null,
            is_string($item['status'] ?? null) ? mb_substr($item['status'], 0, 100) : null,
            $this->genres($item),
            alternativeTitles: $this->alternativeTitles($item, $title),
            airedFrom: $airedFrom,
            airedTo: $this->isoDate(data_get($item, 'aired.to')),
            airedPrecision: $airedPrecision,
            mediaType: $this->text($item['type'] ?? null, 30),
            sourceMaterial: $this->text($item['source'] ?? null, 60),
            season: $season ? mb_strtolower($season) : null,
            seasonYear: $year ?: null,
            duration: $this->text($item['duration'] ?? null, 60),
            ageRating: $this->text($item['rating'] ?? null, 60),
            studios: $this->names($item['studios'] ?? null),
        );
    }

    private function genres(array $item): array
    {
        $genres = [];
        foreach (['genres', 'themes', 'demographics'] as $group) {
            array_push($genres, ...$this->names($item[$group] ?? null));
        }

        return array_values(array_unique($genres));
    }

    private function names(mixed $tags): array
    {
        $names = [];
        foreach (is_array($tags) ? $tags : [] as $tag) {
            if (is_array($tag) && is_string($tag['name'] ?? null)) {
                $names[] = $tag['name'];
            }
        }

        return $names;
    }

    private function alternativeTitles(array $item, string $title): array
    {
        $candidates = is_array($item['titles'] ?? null) ? $item['titles'] : [];
        $candidates[] = ['type' => 'English', 'title' => $item['title_english'] ?? null];
        $candidates[] = ['type' => 'Japanese', 'title' => $item['title_japanese'] ?? null];
        foreach (is_array($item['title_synonyms'] ?? null) ? $item['title_synonyms'] : [] as $synonym) {
            $candidates[] = ['type' => 'Synonym', 'title' => $synonym];
        }

        $seen = [mb_strtolower($title) => true];
        $titles = [];
        foreach ($candidates as $candidate) {
            $name = is_array($candidate) ? $this->text($candidate['title'] ?? null, 255) : null;
            if ($name === null || isset($seen[$key = mb_strtolower($name)])) {
                continue;
            }
            $seen[$key] = true;
            $titles[] = ['type' => $this->text($candidate['type'] ?? null, 30) ?? 'Synonym', 'title' => $name];
        }

        return array_slice($titles, 0, 30);
    }

    /** @return array{0: ?string, 1: ?string} start date and how much of it the source actually knows */
    private function airedStart(array $item): array
    {
        $date = $this->isoDate(data_get($item, 'aired.from'));
        $year = data_get($item, 'aired.prop.from.year');
        $month = data_get($item, 'aired.prop.from.month');
        $day = data_get($item, 'aired.prop.from.day');
        if ($date === null && is_int($year)) {
            $date = $this->isoDate(sprintf('%04d-%02d-%02d', $year, is_int($month) ? $month : 1, is_int($day) && is_int($month) ? $day : 1));
        }
        if ($date === null) {
            return [null, null];
        }
        if (! is_int($year)) {
            return [$date, 'day'];
        }

        return [$date, ! is_int($month) ? 'year' : (is_int($day) ? 'day' : 'month')];
    }

    private function isoDate(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $parts) || ! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return null;
        }

        return $parts[1].'-'.$parts[2].'-'.$parts[3];
    }

    private function text(mixed $value, int $limit): ?string
    {
        return is_string($value) && trim($value) !== '' ? mb_substr(trim($value), 0, $limit) : null;
    }
}
