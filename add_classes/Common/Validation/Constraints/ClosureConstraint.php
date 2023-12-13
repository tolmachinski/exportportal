<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;
use Closure;

class ClosureConstraint extends AbstractConstraint
{
    const TYPE = 'closure_constraint';

    /**
     * The closure that is beign verified. Must return the boolen value.
     */
    private Closure $closureInstance;

    /**
     * The message shown on error.
     */
    private string $failureMessage;

    /**
     * Creates the constraint.
     */
    public function __construct(Closure $closureInstance, ?string $failureMessage = null)
    {
        parent::__construct([]);

        $this->closureInstance = $closureInstance;
        $this->failureMessage = $failureMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function assert(ValidationDataInterface $data)
    {
        if (!$this->closureInstance->call($this, $data)) {
            throw (new ValidationException('The validation failed with errors.'))->setValidationErrors(new ConstraintViolationList([
                new ConstraintViolation(
                    true,
                    false,
                    $this,
                    $this->failureMessage ?? 'The closure returned falsely value.'
                ),
            ]));
        }
    }
}
