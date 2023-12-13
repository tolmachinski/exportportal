<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait ItemValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the item information.
     *
     * @param array $data
     * @param array $category
     * @param int   $max_year
     * @param array $errors
     *
     * @return bool
     */
    protected function validateItem(array $data, array $category, int $max_year, array &$errors = array())
    {
        $rules = $this->getItemValidationRules($category, $max_year);
        $validator = $this->getValidator();
        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = $data;
        $validator->set_rules($rules);
        if (!$validator->validate()) {
            $errors = $validator->get_array_errors();

            return false;
        }

        return true;
    }

    /**
     * Returns the item validation rules.
     *
     * @param array $category
     * @param int $category
     *
     * @return (mixed|mixed[])[][]
     */
    private function getItemValidationRules(array $category, int $max_year)
    {
        $validation_rules = array(
            'hs_tariff_number' => array(
                'field' => 'hs_tariff_number',
                'label' => 'Harmonized Tariff Schedule',
                'rules' => array('required' => '', 'hs_tarif_number' => '', 'max_len[13]' => ''),
            ),
            'title'            => array(
                'field' => 'title',
                'label' => 'Title',
                'rules' => array('required' => '', 'valide_title' => '', 'min_len[4]' => '', 'max_len[255]' => ''),
            ),
            'price_in_dol'     => array(
                'field' => 'price_in_dol',
                'label' => 'Price',
                'rules' => array('required' => '', 'positive_number' => '', 'min[1]' => ''),
            ),
            'final_price'      => array(
                'field' => 'final_price',
                'label' => 'Discount Price',
                'rules' => array(
                    'positive_number'                           => '',
                    'less_than_or_equal_to_field[price_in_dol]' => 'The Discount Price should be less than or equal to Price.',
                    function ($attr, $value, $fail) {
                        if (is_numeric($value) && !$this->getValidator()->greater_than_or_equal($value, 0)) {
                            $fail('The Discount Price should be greater than zero if not empty.');
                        }
                    },
                ),
            ),
            'quantity'         => array(
                'field' => 'quantity',
                'label' => 'Total Quantity',
                'rules' => array('required' => '', 'natural' => ''),
            ),
            'item_length'      => array(
                'field' => 'item_length',
                'label' => 'Length',
                'rules' => array('required' => '', 'item_size' => ''),
            ),
            'item_width'       => array(
                'field' => 'item_width',
                'label' => 'Width',
                'rules' => array('required' => '', 'item_size' => ''),
            ),
            'item_height'      => array(
                'field' => 'item_height',
                'label' => 'Height',
                'rules' => array('required' => '', 'item_size' => ''),
            ),
            'weight'           => array(
                'field' => 'weight',
                'label' => 'Weight',
                'rules' => array('required' => '', 'min[0.001]' => ''),
            ),
            'year'             => array(
                'field' => 'year',
                'label' => 'Year',
                'rules' => array('required' => '', 'natural' => '', 'min[1]'=>'', 'max[' . $max_year . ']'=>''),
            ),
            'port_country'     => array(
                'field' => 'port_country',
                'label' => 'Country',
                'rules' => array(
                    'required' => '',
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_country($value)) {
                            $fail('The Country is not valid.');
                        }
                    },
                ),
            ),
            'states'           => array(
                'field' => 'states',
                'label' => 'State / Region',
                'rules' => array(
                    'required' => '',
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_state($value)) {
                            $fail('The State / Region is not valid.');
                        }
                    },
                ),
            ),
            'port_city'        => array(
                'field' => 'port_city',
                'label' => 'City',
                'rules' => array(
                    'required' => '',
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_city($value)) {
                            $fail('The City is not valid.');
                        }
                    },
                ),
            ),
            'zip'              => array(
                'field' => 'zip',
                'label' => 'ZIP',
                'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => ''),
            ),
            'origin_country'   => array(
                'field' => 'origin_country',
                'label' => 'Country of origin',
                'rules' => array(
                    'required' => '',
                    'natural'  => '',
                    function ($attr, $value, $fail) {
                        if (!empty($value) && !model('country')->has_country($value)) {
                            $fail('The Country of origin is not valid.');
                        }
                    },
                ),
            ),
            'description'      => array(
                'field' => 'description',
                'label' => 'Description',
                'rules' => array('required' => ''),
            ),
            'video'            => array(
                'field' => 'video',
                'label' => 'Video',
                'rules' => array('valid_url' => '', 'max_len[200]' => ''),
            ),
            'terms_cond'       => array(
                'field' => 'terms_cond',
                'label' => 'Product Listing Policy',
                'rules' => array('required' => ''),
            ),
        );

        if (2 === (int) $category['p_or_m']) {
            if ($category['vin']) {
                $validation_rules['vin_code'] = array(
                    'field' => 'vin_code',
                    'label' => 'VIN',
                    'rules' => array('required' => '', 'exact_len[17]' => ''),
                );
            }
        } else {
            $validation_rules['unit_type'] = array(
                'field' => 'unit_type',
                'label' => 'Unit type',
                'rules' => array('required' => '', 'natural' => ''),
            );

            $validation_rules['min_quantity'] = array(
                'field' => 'min_quantity',
                'label' => 'Minimal sale quantity',
                'rules' => array(
                    'required'                              => '',
                    'natural'                               => '',
                    'less_than_or_equal_to_field[quantity]' => 'The Min. sale quantity cannot be greater than Total Quantity.',
                ),
            );
        }

        return $validation_rules;
    }
}
