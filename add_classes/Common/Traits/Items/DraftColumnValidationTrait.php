<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;
use TinyMVC_Library_validator;

/**
 * @deprecated
 */
trait DraftColumnValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the item information.
     *
     * @param array $columnMetadata
     * @param array $data
     * @param array $errors
     *
     * @return bool
     */
    protected function validateDraftColumn(array $columnMetadata, array $column, array &$errors = array())
    {
        $validator = $this->getValidator();
        $rules = $this->getDraftColumnValidationRules($validator, $columnMetadata);
        if (empty($rules)) {
            return true;
        }

        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = $column;
        $validator->set_rules($rules);
        if (!$validator->validate()) {
            $errors = $validator->get_array_errors();

            return false;
        }

        return true;
    }

    /**
     * Returns the validation rules for draft column.
     *
     * @return (mixed|mixed[])[][]
     */
    private function getDraftColumnValidationRules(TinyMVC_Library_validator $validator, array $columnMetadata = array())
    {
        $rules = array();
        $rulesTemplates = $this->getDraftColumnValidationRulesTemplates($validator);
        foreach ($columnMetadata as $columName => $metadata) {
            if (!isset($rulesTemplates[$columName]) || empty($metadata['validation_column'])) {
                continue;
            }

            $columRules = $rulesTemplates[$columName];
            $columRules['field'] = $metadata['validation_column'];
            $rules[] = $columRules;
        }

        return $rules;
    }

    /**
     * Returns the templates of the validation rules for draft column.
     *
     * @return (mixed|mixed[])[][]
     */
    private function getDraftColumnValidationRulesTemplates(TinyMVC_Library_validator $validator)
    {
        return array(
            'title'             => array(
                'field' => null,
                'label' => 'Title',
                'rules' => array(
                    'required'     => '',
                    'valide_title' => '',
                    'min_len[4]'   => '',
                    'max_len[255]' => '',
                ),
            ),
            'price'             => array(
                'field' => null,
                'label' => 'Price',
                'rules' => array(
                    'positive_number' => '',
                    function ($attr, $value, $fail) use ($validator) {
                        if (null !== $value) {
                            $price = priceToUsdMoney($value);
                            if (!$price->isZero() && !$validator->min($value, 1)) {
                                $fail(sprintf('Field "%s" cannot contain a value less than %s.', $attr, 1));
                            }
                        }
                    },
                ),
            ),
            'discount_price'    => array(
                'field' => null,
                'label' => 'Discount Price',
                'rules' => array(
                    'positive_number' => '',
                    function ($attr, $value, $fail) use ($validator) {
                        if (!empty($value) && !$validator->less_than_or_equal_to_field($value, 'price')) {
                            $fail('The Discount Price should be less than or equal to Price.');
                        }
                    },
                    function ($attr, $value, $fail) use ($validator) {
                        if (is_numeric($value) && !$validator->greater_than_or_equal($value, 0)) {
                            $fail('The Discount Price should be greater than zero if not empty.');
                        }
                    },
                ),
            ),
            'quantity'          => array(
                'field' => null,
                'label' => 'Quantity',
                'rules' => array(
                    'natural'  => '',
                ),
            ),
            'min_sale_quantity' => array(
                'field' => null,
                'label' => 'Minimal sale quantity',
                'rules' => array(
                    'natural' => '',
                    function ($attr, $value, $fail) use ($validator) {
                        if (!empty($value) && !$validator->less_than_or_equal_to_field($value, 'quantity')) {
                            $fail('The Min. sale quantity cannot be greater than Total Quantity.');
                        }
                    },
                ),
            ),
            'weight'            => array(
                'field' => null,
                'label' => 'Weight',
                'rules' => array(
                    function ($attr, $value, $fail) use ($validator) {
                        if (!empty($value) && !$validator->min($value, 0.001)) {
                            $fail(sprintf('Field "%s" cannot contain a value less than %s.', $attr, 0.001));
                        }
                    },
                ),
            ),
            'sizes'             => array(
                'field' => null,
                'label' => 'Sizes, (cm) LxWxH',
                'rules' => array('item_sizes' => ''),
            ),
            'video'             => array(
                'field' => null,
                'label' => 'Video',
                'rules' => array(
                    'valid_url'    => '',
                    'max_len[200]' => '',
                ),
            ),
        );
    }
}
