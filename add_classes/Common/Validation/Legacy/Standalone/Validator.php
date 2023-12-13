<?php

namespace App\Common\Validation\Legacy\Standalone;

use App\Common\Validation\Legacy\ConstraintList;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AbstractValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Validator extends AbstractValidator
{
    /**
     * List of the fields.
     *
     * @var ParameterBag
     */
    private $fieldList;

    /**
     * List of the labels.
     *
     * @var ParameterBag
     */
    private $labelsList;

    /**
     * List of the messages.
     *
     * @var ParameterBag
     */
    private $messagesList;

    /**
     * Creates legacy validator.
     */
    public function __construct(ValidatorAdapter $validator, ?array $messages = null, ?array $labels = null, ?array $fields = null)
    {
        $this->fieldList = new ParameterBag(array_merge($this->fields(), $fields ?? array()));
        $this->labelsList = new ParameterBag(array_merge($this->labels(), $labels ?? array()));
        $this->messagesList = new ParameterBag(array_merge($this->messages(), $messages ?? array()));

        parent::__construct(new ConstraintList($this->getRules()), $validator);
    }

    /**
     * Returns the raw rules.
     */
    public function getRules(): array
    {
        return $this->rules();
    }

    /**
     * Returns the bag with fields names.
     */
    public function getFields(): ParameterBag
    {
        return $this->fieldList;
    }

    /**
     * Returns the field labels names.
     */
    public function getLabels(): ParameterBag
    {
        return $this->labelsList;
    }

    /**
     * Returns the custom validation messages.
     */
    public function getMessages(): ParameterBag
    {
        return $this->messagesList;
    }

    /**
     * Returns the rules metadata array.
     */
    protected function rules(): array
    {
        return array();
    }

    /**
     * Returns the fiels preset array.
     */
    protected function fields(): array
    {
        return array();
    }

    /**
     * Returns the labels preset array.
     */
    protected function labels(): array
    {
        return array();
    }

    /**
     * Returns the messages preset array.
     */
    protected function messages(): array
    {
        return array();
    }
}
