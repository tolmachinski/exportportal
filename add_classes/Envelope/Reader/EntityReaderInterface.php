<?php

declare(strict_types=1);

namespace App\Envelope\Reader;

interface EntityReaderInterface
{
    /**
     * Reads the entity.
     *
     * @param mixed $entityId
     */
    public function getEntity($entityId): array;
}
