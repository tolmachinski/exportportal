<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use Doctrine\Common\Collections\Collection;

class HasOne extends HasOneOrMany
{
    /**
     * {@inheritdoc}
     */
    public function match(array &$records, Collection $results, string $relation): array
    {
        return $this->matchRecords($records, $results, $relation, 'one');
    }
}
