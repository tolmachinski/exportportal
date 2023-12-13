<?php

declare(strict_types=1);

namespace App\Common\Assets;

use EntrypointNotFoundException;

interface EntrypointLookupInterface extends ServiceResetInterface
{
    /**
     * Get JS files for given entry.
     *
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     */
    public function getJSFiles(string $entryName): iterable;

    /**
     * Get CSS files for given entry.
     *
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     */
    public function getCssFiles(string $entryName): iterable;

    /**
     * Get all entries.
     */
    public function getAllEntries(): array;
}
