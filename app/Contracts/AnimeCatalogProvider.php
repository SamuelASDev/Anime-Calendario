<?php

namespace App\Contracts;

use App\Data\CatalogAnime;

interface AnimeCatalogProvider
{
    public function cacheIdentity(): string;

    public function sourceName(): string;

    /** @return list<CatalogAnime> */
    public function search(string $query): array;
}
