<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\FlatValidationData;

final class AdditionalSocialFieldsValidator extends Validator
{

    protected $rules = [];

    public function __construct(ValidatorAdapter $validatorAdapter, ?array $rules = null)
    {
        $this->rules = $rules;
        $this->additionalFieldsData = new FlatValidationData();
        parent::__construct($validatorAdapter);
    }

    public function validate($validationData): bool
    {
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? []);
        $validationData->merge($this->getAdditionalFieldsValidationData());

        return parent::validate($validationData);
    }

    /**
     * Get the validation data for recipients.
     */
    protected function getAdditionalFieldsValidationData(): ValidationDataInterface
    {
        return $this->additionalFieldsData;
    }

    protected function rules(): array
    {
        return $this->rules;
    }

}
