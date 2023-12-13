<?php

namespace App\Common\Validation\Standalone;

use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\ConstraintViolationListInterface;

interface ValidatorInterface
{
    /**
     * Validates the stored set of data.
     *
     * @param mixed $validationData
     */
    public function validate($validationData): bool;

    /**
     * Returns the list of constraints for validator.
     */
    public function getConstraints(): ConstraintListInterface;

    /**
     * Returns the list of constraint violations.
     */
    public function getViolations(): ConstraintViolationListInterface;
}
