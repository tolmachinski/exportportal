<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Country_Model;

class AddressValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return array(
            array(
                'field' => $fields->get('country') ?? 'country',
                'label' => $labels->get('country'),
                'rules' => array(
                    'required' => sprintf($messages->get('country.required', ''), $labels->get('country')),
                    'natural'  => sprintf($messages->get('country.natural', ''), $labels->get('country')),
                    function ($attr, $value, $fail) use ($messages) {
                        if (!empty($value) && !model(Country_Model::class)->has_country($value)) {
                            $fail(sprintf($messages->get('country.valid', ''), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => $fields->get('state') ?? 'state',
                'label' => $labels->get('state'),
                'rules' => array(
                    'required' => sprintf($messages->get('state.required', ''), $labels->get('state')),
                    'natural'  => sprintf($messages->get('state.natural', ''), $labels->get('state')),
                    function ($attr, $value, $fail) use ($messages, $fields) {
                        if (!empty($value) && !model(Country_Model::class)->has_state($value, (int) $this->getValidationData()->get($fields->get('country') ?? 'country'))) {
                            $fail(sprintf($messages->get('state.valid', ''), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => $fields->get('city') ?? 'city',
                'label' => $labels->get('city'),
                'rules' => array(
                    'required' => sprintf($messages->get('city.required', ''), $labels->get('city')),
                    'natural'  => sprintf($messages->get('city.natural', ''), $labels->get('city')),
                    function ($attr, $value, $fail) use ($messages, $fields) {
                        if (!empty($value) && !model(Country_Model::class)->has_city($value, (int) $this->getValidationData()->get($fields->get('state') ?? 'state'))) {
                            $fail(sprintf($messages->get('city.valid', ''), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => $fields->get('address') ?? 'address',
                'label' => $labels->get('address'),
                'rules' => array(
                    'required'     => sprintf($messages->get('address.required', ''), $labels->get('address')),
                    'min_len[3]'   => sprintf($messages->get('address.minLength', ''), $labels->get('address')),
                    'max_len[255]' => sprintf($messages->get('address.maxLength', ''), $labels->get('address')),
                ),
            ),
            array(
                'field' => $fields->get('postalCode') ?? 'postal_code',
                'label' => $labels->get('postalCode'),
                'rules' => array(
                    'required'    => sprintf($messages->get('postalCode.required', ''), $labels->get('postalCode')),
                    'max_len[20]' => sprintf($messages->get('postalCode.maxLength', ''), $labels->get('postalCode')),
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'city'       => 'City',
            'state'      => 'State / Region',
            'country'    => 'Country',
            'address'    => 'Address',
            'postalCode' => 'Zip/Postal Code',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'city.valid'           => translate('validation_not_valid_city'),
            'state.valid'          => translate('validation_not_valid_state'),
            'country.valid'        => translate('validation_not_valid_country'),
            'address.minLength'    => translate('validation_address_max_length'),
            'address.maxLength'    => translate('validation_address_max_length'),
            'postalCode.maxLength' => translate('validation_postal_code_max_length'),
        );
    }
}
