<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait DraftImportValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the item information.
     *
     * @param array $data
     * @param array $category
     * @param array $errors
     *
     * @return bool
     */
    protected function validateDraftImport(array $data, array &$errors = array())
    {
        $rules = $this->getItemDraftsImportValidationRules();
        if (empty($rules)) {
            return true;
        }

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
     * Returns the item validation rules for bulk import.
     *
     * @return (mixed|mixed[])[][]
     */
    private function getItemDraftsImportValidationRules()
    {
        return array(
            array(
                'field' => 'title',
                'label' => 'Title',
                'rules' => array(
                    'required' => '',
                ),
            ),
            array(
                'field' => 'file',
                'label' => 'File',
                'rules' => array(
                    'required' => '',
                    function ($attr, $value, $fail) {
                        if (!file_exists($value)) {
                            $fail('The file upload information does not exist.');
                        }
                    },
                ),
            ),
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
