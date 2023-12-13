<?php

namespace App\Common\Validation\Legacy;

use App\Common\Validation\Legacy\ConstraintInterface as LegacyConstraintInterface;
use App\Common\Validation\Legacy\ConstraintList as LegacyConstraintList;

trait IndexAwareValidatorTrait
{
    /**
     * Changes index for the validator.
     */
    public function applyIndex(?int $index): void
    {
        $constraints = $this->getConstraints();
        if ($constraints instanceof LegacyConstraintList) {
            /** @var LegacyConstraintInterface $constraint */
            foreach ($constraints as $constraint) {
                $constraint->setMetadata($this->applyIndexToConstraintMetadata(
                    $constraint->getMetadata(),
                    $index
                ));
            }
        }
    }

    /**
     * Updates the NULL contraint with index value.
     */
    private function applyIndexToConstraintMetadata(array $metadata, ?int $index): array
    {
        if (empty($metadata['indexed_label'])) {
            return $metadata;
        }

        $metadata['label'] = \sprintf($metadata['indexed_label'], $index + 1);

        return $metadata;
    }
}
