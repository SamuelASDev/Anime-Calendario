<?php

namespace App\Data;

final readonly class CatalogAnime
{
    public function __construct(
        public int $malId,
        public string $title,
        public string $imageUrl,
        public ?int $episodes = null,
        public ?string $synopsis = null,
        public ?string $status = null,
        public array $genres = [],
    ) {}
}
