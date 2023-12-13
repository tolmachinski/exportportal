<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait UserAttributesValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the user attributes.
     *
     * @param array $attributes
     * @param array $errors
     *
     * @return bool
     */
    protected function validateUserAttributes(array $attributes, array &$errors = array())
    {
        if (empty($attributes)) {
            return true;
        }

        $attributeNames = arrayGet($attributes, 'name', array());
        $attributeValues = arrayGet($attributes, 'val', array());
        if (count($attributeNames) !== count($attributeValues)) {
            $errors['user_attributes_malformed'] = 'The user attributes are malformed.';
        }

        $attributeNames = array_combine(array_map(function ($key) { return "names_{$key}"; }, range(0, count($attributeNames) - 1)), $attributeNames);
        $attributeValues = array_combine(array_map(function ($key) { return "values_{$key}"; }, range(0, count($attributeValues) - 1)), $attributeValues);
        $rules = $this->makeUserAttributesValidationRules($attributeNames, $attributeValues);
        if (empty($rules)) {
            return true;
        }

        $validator = $this->getValidator();
        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = array_merge($attributeNames, $attributeValues);
        $validator->set_rules($rules);
        if (!$validator->validate()) {
            $errors = array_merge($errors, $validator->get_array_errors());

            return false;
        }

        return empty($errors) ? true : false;
    }

    private function makeUserAttributesValidationRules(array $attributeNames, array $attributeValues)
    {
        $rules = array();
        $index = 1;
        foreach ($attributeNames as $key => $value) {
            $rules[] = array(
                'field' => $key,
                'label' => sprintf('Component #%s', $index),
                'rules' => array(
                    'required'    => sprintf('The Component #%s is required', $index, 3),
                    'max_len[50]' => sprintf('The Component #%s cannot contain more than than %d characters', $index, 50),
                    function ($attr, $value, $fail) use ($index) {
                        if (!empty($value) && mb_strlen($value) !== mb_strlen(cleanInput($value))) {
                            $fail(sprintf('The Component #%s contains unacceptable content.', $index));
                        }
                    },
                ),
            );

            ++$index;
        }

        $index = 1;
        foreach ($attributeValues as $key => $value) {
            $rules[] = array(
                'field' => $key,
                'label' => sprintf('Specification #%s', $index),
                'rules' => array(
                    'required'    => sprintf('The Specification #%s is required', $index, 3),
                    'max_len[50]' => sprintf('The Specification #%s cannot contain more than than %d characters', $index, 50),
                    function ($attr, $value, $fail) use ($index) {
                        if (!empty($value) && mb_strlen($value) !== mb_strlen(cleanInput($value))) {
                            $fail(sprintf('The Specification #%s contains unacceptable content.', $index));
                        }
                    },
                ),
            );

            ++$index;
        }

        return $rules;
    }
}
