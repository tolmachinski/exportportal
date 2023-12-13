<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class DraftConfigurationsValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'country_of_origin',
                'label' => 'Country of origin',
                'rules' => array(
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_country($value)) {
                            $fail('The Country of origin does not appear to be valid. Please choose the correct one from the list.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'product_country',
                'label' => 'Country',
                'rules' => array(
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_country($value)) {
                            $fail('The Product(s) location Country does not appear to be valid. Please choose the correct one from the list.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'product_state',
                'label' => 'State / Region',
                'rules' => array(
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_state($value)) {
                            $fail('The Product(s) location State or Province does not appear to be valid. Please choose the correct one from the list.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'product_city',
                'label' => 'City',
                'rules' => array(
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_city($value)) {
                            $fail('The Product(s) location City does not appear to be valid. Please choose the correct one from the list.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'category',
                'label' => 'Product(s) category',
                'rules' => array(
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('category')->get_category($value)) {
                            $fail('The Product(s) category does not appear to be valid. Please choose the correct one from the list.');
                        }
                    },
                ),
            ),
        );
    }
}
