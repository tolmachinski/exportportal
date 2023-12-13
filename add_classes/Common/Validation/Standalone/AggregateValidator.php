<?php

namespace App\Common\Validation\Standalone;

use App\Common\Validation\ConstraintList;
use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ConstraintViolationListInterface;
use App\Common\Validation\NestedValidationData;
use App\Common\Validation\ValidationDataInterface;
use DomainException;

final class AggregateValidator implements ValidatorInterface
{
    /**
     * The list of aggregated validators.
     *
     * @var ValidatorInterface[]
     */
    private $internalValidators;

    /**
     * The list of violations.
     *
     * @var ConstraintViolationListInterface
     */
    private $violationsList;

    /**
     * Creates the instance of aggregate validator.
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->violationsList = new ConstraintViolationList();
        foreach ($validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                new DomainException(sprintf('The validator must be instance of %s', ValidatorInterface::class));
            }

            $this->internalValidators[] = $validator;
        }
    }

    /**
     * Validates the stored set of data.
     *
     * @param mixed $validationData
     */
    public function validate($validationData): bool
    {
        $violations = $this->getViolations();
        $violations->clear(); // Clear existing violations
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new NestedValidationData((array) $validationData);
        foreach ($this->internalValidators as $validator) {
            /** @var ValidatorInterface $validator */
            if (!$validator->validate($validationData)) {
                $violations->merge($validator->getViolations());
            }
        }

        if ($violations->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns the list of constraints for validator.
     */
    public function getConstraints(): ConstraintListInterface
    {
        $constraintList = new ConstraintList();
        if (!empty($this->internalValidators)) {
            foreach ($this->internalValidators as $validator) {
                $constraintList->merge($validator->getConstraints());
            }
        }

        return $constraintList;
    }

    /**
     * Returns the list of constraint violations.
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violationsList;
    }
}
