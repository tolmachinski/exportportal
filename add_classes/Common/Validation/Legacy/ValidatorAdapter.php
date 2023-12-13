<?php

namespace App\Common\Validation\Legacy;

use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;
use App\Common\Validation\ValidatorInterface;
use Generator;
use Validator as LegacyValidator;

final class ValidatorAdapter implements ValidatorInterface
{
    /**
     * The internal validator.
     *
     * @var LegacyValidator
     */
    private $legacyValidator;

    /**
     * Creates the instance of validation adapter.
     */
    public function __construct(LegacyValidator $legacyValidator)
    {
        $this->legacyValidator = $legacyValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function assert(ValidationDataInterface $data, ConstraintListInterface $constraints)
    {
        $rules = \iterator_to_array($this->unwrapConstraints($constraints));
        $violations = new ConstraintViolationList();
        $validator = $this->legacyValidator;
        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = \iterator_to_array($data->getIterator());
        $validator->set_rules($rules);
        if (!$validator->validate()) {
            foreach ($validator->get_array_errors() as $key => $message) {
                $violations->add(new ConstraintViolation(
                    null,
                    null,
                    $constraints->get($key),
                    $message
                ));
            }
        }

        if ($violations->count() > 0) {
            $violationException = new ValidationException('The validation failed with errors.');
            $violationException->setValidationErrors($violations);

            throw $violationException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ValidationDataInterface $data, ConstraintListInterface $constraints)
    {
        try {
            $this->assert($data, $constraints);
        } catch (ValidationException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Unwraps the constaint list.
     */
    private function unwrapConstraints(ConstraintListInterface $constraints): Generator
    {
        /** @var ConstraintInterface $constraint */
        foreach ($constraints->getIterator() as $key => $constraint) {
            yield $key => $constraint->getMetadata();
        }
    }
}
