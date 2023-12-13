<?php

namespace App\Common\Validation;

class Validator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function assert(ValidationDataInterface $data, ConstraintListInterface $constraints)
    {
        $violations = new ConstraintViolationList();
        foreach ($constraints as $constraint) {
            try {
                $constraint->assert($data);
            } catch (ValidationException $exception) {
                $violations->merge($exception->getValidationErrors());
            }
        }

        if ($violations->count() > 0) {
            $violationException = new ValidationException('The validation failed with errors.');
            $violationException->setValidationErrors($violations);

            throw $violationException;
        }
    }

    /**
     * Validates the dataset agains list of constraints.
     * Fails on the first violation.
     *
     * {@inheritdoc}
     */
    public function validate(ValidationDataInterface $data, ConstraintListInterface $constraints)
    {
        foreach ($constraints as $constraint) {
            try {
                $constraint->assert($data);
            } catch (ValidationException $exception) {
                return false;
            }
        }

        return true;
    }
}
