<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ConstraintInterface;

abstract class AbstractConstraint implements ConstraintInterface
{
    const TYPE = null;

    /**
     * The type of constraint.
     *
     * @var string
     */
    private $type;

    /**
     * The base constraint value.
     *
     * @var string
     */
    private $baseValue;

    /**
     * Creates the constaint.
     */
    public function __construct(array $options = array())
    {
        $this->type = static::TYPE;
        if (isset($options['value'])) {
            $this->baseValue = $options['value'];
        }
    }

    /**
     * Returns the base constraint value.
     *
     * @return mixed
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }
}
