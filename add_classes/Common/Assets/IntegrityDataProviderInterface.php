<?php

declare(strict_types=1);

namespace App\Common\Assets;

interface IntegrityDataProviderInterface
{
    /**
     * Returns a map of integrity hashes indexed by asset paths.
     *
     * @return string[]
     */
    public function getIntegrityData(): array;
}
