<?php

namespace App\Common\Validation\Standalone;

use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ConstraintViolationListInterface;
use App\Common\Validation\NestedValidationData;
use App\Common\Validation\ValidationDataInterface;
use ArrayAccess;
use InvalidArgumentException;
use Traversable;

/**
 * Validator that validates over the data as sequence.
 */
final class SequenceValidator implements ValidatorInterface
{
    /**
     * The internal validator.
     *
     * @var ValidatorInterface
     */
    private $internalValidator;

    /**
     * The list of violations.
     *
     * @var ConstraintViolationListInterface
     */
    private $violationsList;

    /**
     * Creates the instance of sequence validator.
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->violationsList = new ConstraintViolationList();
        $this->internalValidator = $validator;
    }

    /**
     * Validates the stored set of data.
     *
     * @param mixed $validationData
     */
    public function validate($validationData): bool
    {
        if (!$this->isSequence($validationData)) {
            throw new InvalidArgumentException('The provided data must be array, instance of ArrayAccess or traversable.');
        }

        $violations = $this->getViolations();
        $violations->clear(); // Clear existing violations
        foreach ($validationData as $index => $validationEntry) {
            $validator = $this->internalValidator;
            if ($validator instanceof IndexAwareValidatorInterface) {
                $validator->applyIndex($index);
            }

            $validationEntry = $validationEntry instanceof ValidationDataInterface ? $validationEntry : new NestedValidationData((array) $validationEntry);
            /** @var ValidatorInterface $validator */
            if (!$validator->validate($validationEntry)) {
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
        return $this->internalValidator->getConstraints();
    }

    /**
     * Returns the list of constraint violations.
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violationsList;
    }

    /**
     * Checks if provided data can be considered sequence.
     *
     * @param mixed $validationData
     */
    private function isSequence($validationData): bool
    {
        return $validationData instanceof ArrayAccess || $validationData instanceof Traversable || is_array($validationData);
    }
}
