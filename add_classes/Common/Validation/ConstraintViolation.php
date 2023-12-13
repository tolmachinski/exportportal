<?php

namespace App\Common\Validation;

class ConstraintViolation implements ConstraintViolationInterface
{
    /**
     * The value originally passed to the validator.
     *
     * @var mixed
     */
    private $baseValue;

    /**
     * The invalid value that caused this violation.
     *
     * @var mixed
     */
    private $invalidValue;

    /**
     * The constraint whose validation caused the violation.
     *
     * @var null|ConstraintInterface
     */
    private $constraint;

    /**
     * The violation message.
     *
     * @var null|string
     */
    private $message;

    /**
     * The raw violation message.
     *
     * @var null|string
     */
    private $messageTemplate;

    /**
     * The parameters to substitute in the raw violation message.
     *
     * @var array
     */
    private $parameters;

    /**
     * The property path from the base value to the invalid value.
     *
     * @var null|string
     */
    private $propertyPath;

    /**
     * The error code of the violation.
     *
     * @var int
     */
    private $code;

    /**
     * The cause of the violation.
     *
     * @var mixed
     */
    private $cause;

    /**
     * Creates a new constraint violation.
     *
     * @param mixed               $baseValue
     * @param mixed               $invalidValue
     * @param ConstraintInterface $constraint
     * @param string              $message
     * @param string              $messageTemplate
     * @param array               $parameters
     * @param string              $propertyPath
     * @param int                 $code
     * @param mixed               $cause
     */
    public function __construct(
        $baseValue,
        $invalidValue,
        ConstraintInterface $constraint = null,
        $message = null,
        $messageTemplate = null,
        array $parameters = array(),
        $propertyPath = null,
        $code = 0,
        $cause = null
    ) {
        $this->baseValue = $baseValue;
        $this->invalidValue = $invalidValue;
        $this->constraint = $constraint;
        $this->message = $message;
        $this->messageTemplate = $messageTemplate;
        $this->parameters = $parameters;
        $this->propertyPath = $propertyPath;
        $this->code = $code;
        $this->cause = $cause;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    /**
     * Returns the constraint whose validation caused the violation.
     *
     * @return null|ConstraintInterface
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * Returns the cause of the violation.
     *
     * @return mixed
     */
    public function getCause()
    {
        return $this->cause;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }
}
