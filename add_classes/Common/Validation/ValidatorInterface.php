<?php

namespace App\Common\Validation;

interface ValidatorInterface
{
    /**
     * Asserts the dataset against list of constraints.
     *
     * @param ValidationDataInterface $data
     * @param ConstraintListInterface $constraints
     *
     * @throws ValidationException
     */
    public function assert(ValidationDataInterface $data, ConstraintListInterface $constraints);

    /**
     * Validates the dataset against list of constraints.
     *
     * @param ValidationDataInterface $data
     * @param ConstraintListInterface $constraints
     *
     * @return bool
     */
    public function validate(ValidationDataInterface $data, ConstraintListInterface $constraints);
}
