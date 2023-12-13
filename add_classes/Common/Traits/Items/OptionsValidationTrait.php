<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait OptionsValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the item variants.
     *
     * @param array $options
     * @param array $combinations
     * @param array $errors
     *
     * @return bool
     */
    protected function validateItemOptionsAndCombinations(array $options = array(), array $combinations = array(), array &$errors = array())
    {
        list($optionsPostdata, $optionsRules) = $this->getItemOptionsValidationRules($options);
        list($combinationsPostdata, $combinationsRules) = $this->getItemOptionsCombinationsValidationRules($combinations, $options);
        $rules = array_merge($optionsRules, $combinationsRules);
        $postdata = array_merge($optionsPostdata, $combinationsPostdata);

        $validator = $this->getValidator();
        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = $postdata;
        $validator->set_rules($rules);
        if (!empty($rules) && !$validator->validate()) {
            $errors = $validator->get_array_errors();

            return false;
        }

        return true;
    }

    private function getItemOptionsValidationRules(array $options = array())
    {
        if (empty($options)) {
            return array(array(), array());
        }

        $rules = array();
        $extract = array();
        $known_options = array_keys($options);
        $extract['options'] = $options;
        $rules[] = array(
            'field' => 'options',
            'rules' => array(
                function ($attr, $value, $fail) use ($extract) {
                    if (!empty($extract['options']) && !is_array($extract['options'])) {
                        $fail(sprintf('The "Variation type" are malformed. Please try to configure it anew.'));
                    }
                },
                function ($attr, $value, $fail) use ($extract) {
                    if (count($extract['options']) > 4) {
                        $fail('No more that four "Variation type" are allowed for one item.');
                    }
                },
            ),
        );

        foreach ($options as $option_key => $option) {
            $extract["{$option_key}.variants"] = arrayGet($option, 'variants');
            $extract["{$option_key}.group_name"] = arrayGet($option, 'group_name');
            $option_index = array_search($option_key, $known_options) + 1;
            $rules[] = array(
                'field' => "{$option_key}.group_name",
                'rules' => array(
                    'required'    => sprintf('The name of the "Variation type" nr.%s is required.', $option_index),
                    'valid_title' => sprintf("The name of the \"Variation type\" nr.%s must contain only letters, numbers and \'.-\" symbols.", $option_index),
                    'max_len[30]' => sprintf('The name of the "Variation type" nr.%s cannot contain more than %d characters.', $option_index, 50),
                ),
            );
            $rules[] = array(
                'field' => "{$option_key}.variants",
                'rules' => array(
                    'required' => sprintf('The "Variation type" #%s must contain at least one option.', $option_index),
                    function ($attr, $value, $fail) use ($extract, $option_key, $option_index) {
                        if (!is_array($extract["{$option_key}.variants"])) {
                            $fail(sprintf('The values of the "Variation options" nr.%s are invalid.', $option_index));
                        }
                    },
                    function ($attr, $value, $fail) use ($extract, $option_key, $option_index) {
                        if (count($extract["{$option_key}.variants"]) > 10) {
                            $fail(sprintf('The "Variation options" nr.%s cannot contain more than %d values.', $option_index, 10));
                        }
                    },
                ),
            );

            $option_values = arrayGet($option, 'variants', array());
            $known_values = array_keys($option_values);
            foreach ($option_values as $value_key => $value) {
                $extract["{$option_key}.variants.{$value_key}"] = $value;
                $value_index = array_search($value_key, $known_values) + 1;
                $rules[] = array(
                    'field' => "{$option_key}.variants.{$value_key}",
                    'rules' => array(
                        'required'    => sprintf('The value nr.%s of the "Variation options" nr.%s cannot be empty', $option_index, $value_index),
                        'max_len[30]' => sprintf('The value nr.%s of the "Variation options" nr.%s cannot contain more than %d characters.', $option_index, $value_index, 30),
                    ),
                );
            }
        }

        return array($extract, $rules);
    }

    private function getItemOptionsCombinationsValidationRules(array $combinations = array(), array $options = array())
    {
        if (empty($combinations)) {
            return array(array(), array());
        }

        $rules = array();
        $extract = array();
        $known_combinations = array_keys($combinations);
        $options_map = array_combine(
            array_keys($options),
            array_map(
                function ($entry) {
                    ksort($entry);

                    return array_keys($entry);
                },
                dataGet($options, '*.variants')
            )
        );

        foreach ($combinations as $combinations_key => $combination) {
            $extract["{$combinations_key}.combination"] = 1;
            $extract["{$combinations_key}.price"] = arrayGet($combination, 'price');
            $combination_values = arrayGet($combination, 'combination');
            $combination_index = array_search($combinations_key, $known_combinations) + 1;
            $rules[] = array(
                'field' => "{$combinations_key}.combination",
                'rules' => array(
                    'required' => sprintf('The Combination nr.%s must contain at least one value.', $combination_index),
                    function ($attr, $value, $fail) use ($options, $combination_values, $combination_index) {
                        if (
                            !is_array($combination_values)
                            || count($options) !== count($combination_values)
                            || !empty(array_diff(array_keys($combination_values), array_keys($options)))
                        ) {
                            $fail(sprintf('The Combination nr.%s seems to be malformed. Please try to configure it anew.', $combination_index));
                        }
                    },
                    function ($attr, $value, $fail) use ($combination_values, $combination_index, $options_map) {
                        if (!is_array($combination_values)) {
                            return;
                        }

                        foreach ($combination_values as $key => $value) {
                            $is_full_group = is_array($value);
                            if (
                                !isset($options_map[$key])
                                || (!$is_full_group && !in_array($value, $options_map[$key]))
                                || ($is_full_group && !empty(array_diff($options_map[$key], array_values($value))))
                            ) {
                                $fail(sprintf('The Options Combination nr.%s seems to be malformed. Please try to configure it anew.', $combination_index));
                            }
                        }
                    },
                ),
            );
            $rules[] = array(
                'field' => "{$combinations_key}.price",
                'rules' => array(
                    'required'        => sprintf('The Price for Combination nr.%s is required.', $combination_index),
                    'greater_than[0]' => sprintf('The Price for Combination nr.%s must be greater than zero.', $combination_index),
                ),
            );
        }

        $extract['hashed_combinations'] = $this->getItemOptionsCombinationsHashes($combinations);
        $rules[] = array(
            'field' => 'hashed_combinations',
            'rules' => array(
                function ($attr, $value, $fail) use ($extract) {
                    if (
                        !empty($extract['hashed_combinations'])
                        && array_unique($extract['hashed_combinations']) != $extract['hashed_combinations']
                    ) {
                        $fail('Some of Combinations have duplicates. Only unique Combinations are allowed.');
                    }
                },
            ),
        );

        return array($extract, $rules);
    }

    private function getItemOptionsCombinationsHashes(array $combinations = array())
    {
        return array_map(function ($combination) {
            if (isset($combination['combination']) && is_array($combination['combination'])) {
                ksort($combination['combination']);
                foreach ($combination['combination'] as &$combination_value) {
                    if (is_array($combination_value)) {
                        ksort($combination_value);
                    }
                }
            }

            return hash('sha256', \json_encode($combination));
        }, $combinations);
    }
}
