<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait DraftValidationTrait
{
    use ItemValidationTrait;
    use ValidatorAwareTrait {
        ValidatorAwareTrait::getValidator insteadof ItemValidationTrait;
    }

    /**
     * Validates the item draft information.
     *
     * @param array $data
     * @param array $category
     * @param array $max_year
     * @param array $errors
     *
     * @return bool
     */
    protected function validateDraft(array $data, array $category, int $max_year, array &$errors = array())
    {
        $rules = $this->getDraftValidationRules($category, $max_year);
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
     * Returns the item draft validation rules.
     *
     * @param array $category
     * @param int   $max_year
     *
     * @return (mixed|mixed[])[][]
     */
    private function getDraftValidationRules(array $category, int $max_year)
    {
        $validator = $this->getValidator();
        $draftRules = array();
        $skippedRules = array('terms_cond');
        $replacementRules = array(
            'price_in_dol' => array(
                'positive_number' => '',
                function ($attr, $value, $fail) use ($validator) {
                    $price = priceToUsdMoney($value);
                    if (!$price->isZero() && !$validator->min($value, 1)) {
                        $fail(sprintf('Field "%s" cannot contain a value less than %s.', $attr, 1));
                    }
                },
            ),
            'weight'       => array(
                function ($attr, $value, $fail) use ($validator) {
                    if (!empty($value) && !$validator->min($value, 0.001)) {
                        $fail(sprintf('Field "%s" cannot contain a value less than %s.', $attr, 0.001));
                    }
                },
            ),
            'year'         => array(
                function ($attr, $value, $fail) use ($validator) {
                    if (!empty($value) && !$validator->min($value, 1)) {
                        $fail(sprintf('Field "%s" cannot contain a value less than %s.', $attr, 1));
                    }
                },
                'max[' . $max_year . ']' => '',
            ),
        );

        $itemValidationRules = $this->getItemValidationRules($category, $max_year);
        foreach ($itemValidationRules as $key => $fieldMetadata) {
            if (empty($fieldMetadata['rules'])) {
                continue;
            }

            if (in_array($key, $skippedRules)) {
                continue;
            }

            if (isset($replacementRules[$key])) {
                $rules = $replacementRules[$key];
            } else {
                $rules = $fieldMetadata['rules'];
                if (array_key_exists('required', $rules)) {
                    unset($rules['required']);
                }
            }

            $fieldMetadata['rules'] = $rules;
            $draftRules[] = $fieldMetadata;
        }

        return $draftRules;
    }
}
