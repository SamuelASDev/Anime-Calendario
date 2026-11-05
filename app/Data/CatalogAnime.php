<?php

namespace App\Data;

final readonly class CatalogAnime
{
    /**
     * @param  list<string>  $genres
     * @param  list<array{type: string, title: string}>  $alternativeTitles
     * @param  list<string>  $studios
     */
    public function __construct(
        public int $malId,
        public string $title,
        public string $imageUrl,
        public ?int $episodes = null,
        public ?string $synopsis = null,
        public ?string $status = null,
        public array $genres = [],
        public array $alternativeTitles = [],
        public ?string $airedFrom = null,
        public ?string $airedTo = null,
        public ?string $airedPrecision = null,
        public ?string $mediaType = null,
        public ?string $sourceMaterial = null,
        public ?string $season = null,
        public ?int $seasonYear = null,
        public ?string $duration = null,
        public ?string $ageRating = null,
        public array $studios = [],
    ) {}
}
