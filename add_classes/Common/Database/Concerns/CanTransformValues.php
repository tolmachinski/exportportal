<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\DynamicAttributesTransformer;
use App\Common\Database\ValuesTransformerInterface;

/**
 * Allows for the model to perform searches.
 */
trait CanTransformValues
{
    /**
     * Transforms one record's attributes to their database values.
     */
    protected function recordAttributesToDatabaseValues(array $record, array $metadata = [], bool $forced = false): array
    {
        return $this->createAttributesTransfomer($metadata)->transformToDatabaseValues(
            $record,
            $forced
        );
    }

    /**
     * Transforms the attributes from list of records to their databse values.
     */
    protected function recordsListToDatabaseValues(array $record, array $metadata = [], bool $forced = false): array
    {
        $transformer = $this->createAttributesTransfomer($metadata);

        return array_map(
            fn ($record) => $transformer->transformToDatabaseValues($record, $forced),
            array_values($record)
        );
    }

    /**
     * Create atttributes caster for given metadata.
     */
    private function createAttributesTransfomer(array $metadata): ValuesTransformerInterface
    {
        // Normalize metadata to preserve backward compatibility
        foreach ($metadata as &$metaEntry) {
            if (isset($metaEntry['datetime']) && true === $metaEntry['datetime']) {
                $metaEntry['type'] = 'datetime';
            }
        }

        return new DynamicAttributesTransformer(
            $this->getHandler(),
            \array_column(\array_filter($metadata, fn ($d) => !$d['fillable']), 'name'),
            \array_column($metadata, 'type', 'name'),
            \array_column(\array_filter($metadata, fn ($d) => $d['nullable']), 'name')
        );
    }
}
