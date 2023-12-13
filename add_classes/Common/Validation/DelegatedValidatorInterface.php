<?php

namespace App\Common\Validation;

interface DelegatedValidatorInterface
{
    /**
     * Returns the delegated validator instance.
     *
     * @return ValidatorInterface
     */
    public function getDelegatedValidator(): ValidatorInterface;

    /**
     * Replaces the delegated validator,.
     *
     * @param ValidatorInterface $validator
     */
    public function replaceDelegatedValidator(ValidatorInterface $validator): void;
}
