<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use Doctrine\Common\Collections\Collection;

class HasOneThrough extends HasManyThrough
{
    /**
     * {@inheritDoc}
     */
    public function match(array &$records, Collection $results, string $relation): array
    {
        // First, we will take the dictionary for current records and results list
        $dictionary = $this->buildDictionary($results, 'relation__internal_through_key');
        // After that we will spin through the parent results to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work.
        foreach ($records as &$record) {
            if (null !== ($key = $record[$this->localKey] ?? null)) {
                $record[$relation] = $this->getRelationValue($dictionary, $key, 'one');
            } else {
                $record[$relation] = null;
            }
        }

        return $records;
    }
}
