<?php

namespace App\Common\Validation;

trait DelegatedValidatorTrait
{
    /**
     * The delegated validator instance.
     *
     * @var ValidatorInterface
     */
    private $delegatedValidator;

    /**
     * Returns the delegated validator instance.
     *
     * @return ValidatorInterface
     */
    public function getDelegatedValidator(): ValidatorInterface
    {
        return $this->delegatedValidator;
    }

    /**
     * Replaces the delegated validator,.
     *
     * @param ValidatorInterface $validator
     */
    public function replaceDelegatedValidator(ValidatorInterface $validator): void
    {
        $this->delegatedValidator = $validator;
    }
}
