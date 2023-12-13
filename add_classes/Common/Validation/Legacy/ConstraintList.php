<?php

namespace App\Common\Validation\Legacy;

use App\Common\Validation\ConstraintList as BaseConstraintList;
use App\Common\Validation\Legacy\ConstraintInterface;
use App\Common\Validation\Legacy\ConstraintListInterface;
use App\Common\Validation\Legacy\Constraints\NullConstraint;

class ConstraintList extends BaseConstraintList implements ConstraintListInterface
{
    /**
     * Creates a new constraint list.
     *
     * @param ConstraintInterface[]|mixed[] $constraints
     */
    public function __construct(array $constraints = array())
    {
        foreach ($constraints as $constraint) {
            if (!$constraint instanceof ConstraintInterface) {
                $constraint = new NullConstraint($constraint);
            }

            $this->offsetSet($constraint->getMetadata()['field'] ?? null, $constraint);
        }
    }
}
