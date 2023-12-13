<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ValidationDataInterface;
use Money\Money;

abstract class AbstractAmount extends AbstractConstraint
{
    const AMOUNT_ALIAS = 'amount';

    public function __construct(array $options = array())
    {
        parent::__construct($options);
        if (!$this->getBaseValue() instanceof Money) {
            throw new \InvalidArgumentException('The initial value for constraint "%s" must be instance of "Money\\Money" class.');
        }
    }

    /**
     * Resolves the amount from the provided dataset.
     *
     * @param ValidationDataInterface $data
     *
     * @return null|\Money\Money
     */
    protected function resolveAmount(ValidationDataInterface $data)
    {
        $amount = $data->get(static::AMOUNT_ALIAS);
        if (null === $amount || !($amount instanceof Money)) {
            return null;
        }

        return $amount;
    }
}
