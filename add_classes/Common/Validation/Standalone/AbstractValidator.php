<?php

namespace App\Common\Validation\Standalone;

use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ConstraintViolationListInterface;
use App\Common\Validation\DelegatedValidatorInterface;
use App\Common\Validation\DelegatedValidatorTrait;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\NestedValidationData;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;
use App\Common\Validation\Validator as BaseValidator;
use App\Common\Validation\ValidatorInterface as BaseValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface, DelegatedValidatorInterface
{
    use DelegatedValidatorTrait;

    /**
     * The list of violations.
     *
     * @var ConstraintViolationListInterface
     */
    private $violationsList;

    /**
     * The list of violations.
     *
     * @var ConstraintListInterface
     */
    private $constraintList;

    /**
     * The data that is validated.
     *
     * @var null|ValidationDataInterface
     */
    private $validationData;

    /**
     * Creates validator instance.
     */
    public function __construct(ConstraintListInterface $constraints, ?BaseValidatorInterface $internalValidator = null)
    {
        $this->constraintList = $constraints;
        $this->violationsList = new ConstraintViolationList();
        $this->delegatedValidator = $internalValidator ?? new BaseValidator();
    }

    /**
     * Validates the provided set of data.
     *
     * @param mixed $validationData
     */
    public function validate($validationData): bool
    {
        $this->getViolations()->clear(); // Clear previous violations
        $this->setValidationData(
            $validationData instanceof ValidationDataInterface ? $validationData : new NestedValidationData($validationData)
        );

        try {
            $this->getDelegatedValidator()->assert(
                $this->getValidationData(),
                $this->getConstraints()
            );

            return true;
        } catch (ValidationException $exception) {
            $this->getViolations()->merge($exception->getValidationErrors());

            return false;
        }
    }

    /**
     * Returns the list of constraint violations.
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violationsList;
    }

    /**
     * Returns the list of constraints for validator.
     */
    public function getConstraints(): ConstraintListInterface
    {
        return $this->constraintList;
    }

    /**
     * Returns the list of constraints for validator.
     *
     * @return ConstraintListInterface
     */
    public function getValidationData(): ValidationDataInterface
    {
        if (null === $this->validationData) {
            $this->setValidationData(null);
        }

        return $this->validationData;
    }

    /**
     * Set the data that is validated.
     *
     * @param null|ValidationDataInterface $validationData the data that is validated
     */
    public function setValidationData(?ValidationDataInterface $validationData): self
    {
        $this->validationData = $validationData ?? new FlatValidationData();

        return $this;
    }
}
