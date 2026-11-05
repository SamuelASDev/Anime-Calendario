<?php

namespace App\Services\Catalog;

use RuntimeException;

class CatalogRateLimited extends RuntimeException
{
    public function __construct(public readonly int $retryAfter)
    {
        parent::__construct('Catalog rate limit reached.', 429);
    }
}
