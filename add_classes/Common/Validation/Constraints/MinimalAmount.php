<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;

class MinimalAmount extends AbstractAmount
{
    const TYPE = 'minimal_amount';

    /**
     * {@inheritdoc}
     */
    public function assert(ValidationDataInterface $data)
    {
        $baseAmount = $this->getBaseValue();
        $providedAmount = $this->resolveAmount($data);
        if (null === $providedAmount || !$providedAmount->isSameCurrency($baseAmount)) {
            return;
        }

        if (!$providedAmount->greaterThanOrEqual($baseAmount)) {
            $exception = new ValidationException('The validation failed with errors.');
            $exception->setValidationErrors(new ConstraintViolationList(array(
                new ConstraintViolation(
                    $baseAmount,
                    $providedAmount,
                    $this,
                    'The provided amount is less than minimal amount.'
                ),
            )));

            throw $exception;
        }
    }
}
