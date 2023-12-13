<?php

namespace App\Common\Validation;

interface ConstraintViolationInterface
{
    /**
     * Returns the value originally passed to the validator.
     *
     * @return mixed
     */
    public function getBaseValue();

    /**
     * Returns the invalid value that caused this violation.
     *
     * @return mixed
     */
    public function getInvalidValue();

    /**
     * Returns the constraint whose validation caused the violation.
     *
     * @return null|ConstraintInterface
     */
    public function getConstraint();

    /**
     * Returns the violation message.
     *
     * @return null|string
     */
    public function getMessage();

    /**
     * Returns the raw violation message.
     *
     * @return null|string
     */
    public function getMessageTemplate();

    /**
     * Returns the parameters to substitute in the raw violation message.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Returns the property path from the base value to the invalid value.
     *
     * @return null|string
     */
    public function getPropertyPath();

    /**
     * Returns the error code of the violation.
     *
     * @return int
     */
    public function getCode();

    /**
     * Returns the cause of the violation.
     *
     * @return mixed
     */
    public function getCause();
}
