<?php

declare(strict_types=1);

namespace App\Common\Validation;

use Throwable;

/**
 * Exception thrown if a validation error occurs.
 */
class ValidationException extends \RuntimeException
{
    /**
     * List of the validation errors.
     *
     * @var ConstraintViolationListInterface
     */
    private $validationErrors;

    /**
     * Construct the validation exception.
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, ?ConstraintViolationListInterface $validationErrors = null)
    {
        parent::__construct($message, $code, $previous);
        if (null !== $validationErrors) {
            $this->validationErrors = $validationErrors;
        }
    }

    /**
     * Sets the validtion errors list.
     *
     * @param array $validationErrors
     *
     * @return self
     */
    public function setValidationErrors(ConstraintViolationListInterface $validationErrors)
    {
        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * Returns the validation errors list.
     *
     * @return null|ConstraintViolationListInterface
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
