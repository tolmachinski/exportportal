<?php

declare(strict_types=1);

namespace App\Validators;

use Country_Model;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Symfony\Component\HttpFoundation\ParameterBag;
use App\Common\Validation\Legacy\Standalone\Validator;

final class WebinarRequestValidator extends Validator
{
    protected const MIN_NAME_LENGTH = 2;
    protected const MAX_NAME_LENGTH = 50;
    protected const MAX_EMAIL_LENGTH = 100;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'fname'   => [
                'field' => $fields->get('fname'),
                'label' => $labels->get('fname'),
                'rules' => $this->getFirstNameRules(static::MIN_NAME_LENGTH, static::MAX_NAME_LENGTH, $messages),
            ],
            'lname'   => [
                'field' => $fields->get('lname'),
                'label' => $labels->get('lname'),
                'rules' => $this->getLastNameRules(static::MIN_NAME_LENGTH, static::MAX_NAME_LENGTH, $messages),
            ],
            'country' => [
                'field' => $fields->get('country'),
                'label' => $labels->get('country'),
                'rules' => $this->getCountryRules($messages),
            ],
            'userType' => [
                'field' => $fields->get('userType'),
                'label' => $labels->get('userType'),
                'rules' => $this->getUserTypeRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'fname'       => 'fname',
            'lname'       => 'lname',
            'country'     => 'country',
            'userType'    => 'user_type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'fname'       => 'First Name',
            'lname'       => 'Last Name',
            'country'     => 'Country',
            'userType'    => 'User type'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'country.valid' => translate('validation_country_valid'),
        ];
    }

    /**
     * Get the first name field validation rules.
     */
    protected function getFirstNameRules(int $minLength, int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('fname.required') ?? '',
            "min_len[{$minLength}]" => $messages->get('fname.minLength') ?? '',
            "max_len[{$maxLength}]" => $messages->get('fname.maxLength') ?? '',
            'valid_user_name'       => $messages->get('fname.validName') ?? '',
        ];
    }

    /**
     * Get the last name field validation rules.
     */
    protected function getLastNameRules(int $minLength, int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('lname.required') ?? '',
            "min_len[{$minLength}]" => $messages->get('lname.minLength') ?? '',
            "max_len[{$maxLength}]" => $messages->get('lname.maxLength') ?? '',
            'valid_user_name'       => $messages->get('lname.validName') ?? '',
        ];
    }

    /**
     * Get the country validation rule.
     */
    protected function getCountryRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('country.required') ?? '',
            function ($attr, $value, $fail) use ($messages) {
                if (!empty($value) && !model(Country_Model::class)->has_country($value)) {
                    $fail(sprintf($messages->get('country.valid', ''), $attr));
                }
            },
        ];
    }

    /**
     * Get the user type validation rule.
     */
    protected function getUserTypeRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('userType.required') ?? '',
        ];
    }
}
