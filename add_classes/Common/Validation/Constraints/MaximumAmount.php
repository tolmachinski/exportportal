<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;

class MaximumAmount extends AbstractAmount
{
    const TYPE = 'maximum_amount';

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

        if (!$providedAmount->lessThanOrEqual($baseAmount)) {
            $exception = new ValidationException('The validation failed with errors.');
            $exception->setValidationErrors(new ConstraintViolationList(array(
                new ConstraintViolation(
                    $baseAmount,
                    $providedAmount,
                    $this,
                    'The provided amount is greater than maximum amount.'
                ),
            )));

            throw $exception;
        }
    }
}
