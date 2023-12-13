<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ItemVariantsValidatorLegacy extends Validator
{
    protected const MIN_PRICE = 0.01;

    protected const MAX_PRICE = 9999999999.99;

    /**
     * The list of options.
     *
     * @var mixed
     */
    private $options;

    /**
     * The list of combintations.
     *
     * @var mixed
     */
    private $combinations;

    /**
     * The validation data for variants.
     *
     * @var ValidationDataInterface
     */
    private $variantsValidationData;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        $options,
        $combinations,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->options = $options;
        $this->combinations = $combinations;
        $this->variantsValidationData = new FlatValidationData();

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? array());
        $validationData->merge($this->getVariantsValidationData());

        return parent::validate($validationData);
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function maxValidation(): array
    {
        return array(
            'max_variation_option'              => (int) config('item_max_variation_option', 5),
            'max_variation_option_characters'   => (int) config('item_max_variation_option_characters', 30),
            'max_variation_values'              => (int) config('item_max_variation_values', 15)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'options.malformed'                    => 'The "Variation type" are malformed. Please try to configure it anew.',
            'options.maxAmount'                    => 'No more that four "Variation type" are allowed for one item.',
            'options.groupName.requred'            => 'The name of the "Variation type" #%s is required.',
            'options.groupName.valid'              => "The name of the \"Variation type\" #%s must contain only letters, numbers and \\'.-\" symbols.",
            'options.groupName.maxlength'          => 'The name of the "Variation type" #%s cannot contain more than %d characters.',
            'options.variants.requred'             => 'The "Variation type" #%s must contain at least one option.',
            'options.variants.valid'               => 'The values of the "Variation options" #%s are invalid.',
            'options.variants.maxAmount'           => 'The "Variation options" #%s cannot contain more than %d values.',
            'options.variants.value.requred'       => 'The value #%s of the "Variation options" #%s cannot be empty',
            'options.variants.value.maxLength'     => 'The value #%s of the "Variation options" #.%s cannot contain more than %d characters.',
            'combinations.malformed'               => 'The "Combinations" are malformed. Please try to configure it anew.',
            'combinations.record.required'         => 'The Combination #%s must contain at least one value.',
            'combinations.record.malformed'        => 'The Combination #%s seems to be malformed. Please try to configure it anew.',
            'combinations.record.values.malformed' => 'The Options Combination #%s seems to be malformed. Please try to configure it anew.',
            'combinations.record.price.required'   => 'The Price for Combination #%s is required.',
            'combinations.record.price.min'        => 'The Price for Combination #%s must be greater than %s.',
            'combinations.record.price.max'        => 'The Price for Combination #%s must be less than %s.',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return array_merge(
            $this->makeOptionsRules($options = $this->getOptions(), $messages),
            $this->makeCombinationsRules($this->getCombinations(), $options, $messages)
        );
    }

    /**
     * Get the list of options.
     *
     * @return mixed
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the list of combintations.
     *
     * @return mixed
     */
    protected function getCombinations()
    {
        return $this->combinations;
    }

    /**
     * Get the validation data for variants.
     */
    protected function getVariantsValidationData(): ValidationDataInterface
    {
        return $this->variantsValidationData;
    }

    /**
     * Makes the validation rules for options.
     *
     * @param mixed $options
     */
    private function makeOptionsRules($options, ParameterBag $messages): array
    {
        if (empty($options)) {
            return array();
        }

        $rules = array();
        $validationData = $this->getVariantsValidationData();
        $maxValidation = $this->maxValidation();
        $validationData->set('item:options', is_array($options) ? new ArrayCollection($options) : $options);
        $rules[] = array(
            'field' => 'item:options',
            'rules' => array(
                function (string $attr, $value, callable $fail) use ($messages) {
                    if (!empty($value) && !$value instanceof Collection) {
                        $fail(sprintf($messages->get('options.malformed'), $attr));
                    }
                },
                function (string $attr, $value, callable $fail) use ($messages, $maxValidation) {
                    if ($value instanceof Collection && $value->count() > $maxValidation['max_variation_option']) {
                        $fail(sprintf($messages->get('options.maxAmount'), $attr));
                    }
                },
            ),
        );

        if (!is_array($options)) {
            return $rules;
        }

        $knownOptions = array_keys($options);
        foreach ($options as $optionKey => $option) {
            $optionIndex = array_search($optionKey, $knownOptions) + 1;

            $validationData->set("item:options:{$optionKey}.group_name", $option['group_name'] ?? null);
            $rules[] = array(
                'field' => "item:options:{$optionKey}.group_name",
                'rules' => array(
                    'required'    => sprintf($messages->get('options.groupName.requred'), $optionIndex),
                    'valid_title' => sprintf($messages->get('options.groupName.valid'), $optionIndex),
                   // 'max_len[30]' => sprintf($messages->get('options.groupName.maxlength'), $optionIndex, 30),
                ),
            );

            $variants = $option['variants'] ?? null;
            $validationData->set("item:options:{$optionKey}.variants", is_array($variants) ? new ArrayCollection($variants) : $variants);
            $rules[] = array(
                'field' => "item:options:{$optionKey}.variants",
                'rules' => array(
                    'required' => sprintf($messages->get('options.variants.requred'), $optionIndex),
                    function ($attr, $value, $fail) use ($optionIndex, $messages) {
                        if (!$value instanceof Collection) {
                            $fail(sprintf($messages->get('options.variants.valid'), $optionIndex));
                        }
                    },
                    // function ($attr, $value, $fail) use ($optionIndex, $messages, $maxValidation) {
                    //     if ($value instanceof Collection && $value->count() > $maxValidation['max_variation_values']) {
                    //         $fail(sprintf($messages->get('options.variants.maxAmount'), $optionIndex, $maxValidation['max_variation_values']));
                    //     }
                    // },
                ),
            );

            if (!is_array($variants)) {
                continue;
            }

            $optionValues = $variants;
            $knownValues = array_keys($optionValues);
            foreach ($optionValues as $valueKey => $value) {
                $validationData->set("{$optionKey}.variants.{$valueKey}", $value);
                $valueIndex = array_search($valueKey, $knownValues) + 1;
                $rules[] = array(
                    'field' => "{$optionKey}.variants.{$valueKey}",
                    'rules' => array(
                        'required'    => sprintf($messages->get('options.variants.value.requred'), $optionIndex, $valueIndex),
                       // 'max_len[' . $maxValidation['max_variation_option_characters'] . ']' => sprintf($messages->get('options.variants.value.maxLength'), $optionIndex, $valueIndex, $maxValidation['max_variation_option_characters']),
                    ),
                );
            }
        }

        return $rules;
    }

    /**
     * Makes the validation rules for combinations.
     *
     * @param mixed $combinations
     * @param mixed $options
     */
    private function makeCombinationsRules($combinations, $options, ParameterBag $messages): array
    {
        if (empty($combinations)) {
            return array();
        }

        $rules = array();
        $validationData = $this->getVariantsValidationData();
        $validationData->set('item:combinations', is_array($combinations) ? new ArrayCollection($combinations) : $combinations);
        $rules[] = array(
            'field' => 'item:combinations',
            'rules' => array(
                function (string $attr, $value, callable $fail) use ($messages) {
                    if (!empty($value) && !$value instanceof Collection) {
                        $fail(sprintf($messages->get('combinations.malformed'), $attr));
                    }
                },
            ),
        );

        if (!is_array($combinations)) {
            return $rules;
        }

        $validationData->set('item:combinations:hashes', new ArrayCollection($this->getCombinationHashes($combinations)));
        $rules[] = array(
            'field' => 'item:combinations:hashes',
            'rules' => array(
                function (string $attr, Collection $value, callable $fail) {
                    if (
                        $value->count() > 0
                        && array_unique($value->getValues()) != $value->getValues()
                    ) {
                        $fail('Some of Combinations have duplicates. Only unique Combinations are allowed.');
                    }
                },
            ),
        );

        $knownCombinations = array_keys($combinations);
        $optionsMap = is_array($options)
            ? array_combine(
                array_keys($options),
                array_map(
                    function ($entry) {
                        ksort($entry);

                        return array_keys($entry);
                    },
                    dataGet($options, '*.variants')
                )
            )
            : array();

        foreach ($combinations as $combinationsKey => $combination) {
            $validationData->set("item:combinations:{$combinationsKey}.combination", 1);
            $validationData->set("item:combinations:{$combinationsKey}.price", $combination['price'] ?? null);
            $validationData->set("item:combinations:{$combinationsKey}.img", $combination['img'] ?? null);

            $combinationValues = $combination['combination'];
            $combinationIndex = array_search($combinationsKey, $knownCombinations) + 1;
            $rules[] = array(
                'field' => "item:combinations:{$combinationsKey}.combination",
                'rules' => array(
                    'required' => sprintf($messages->get('combinations.record.required'), $combinationIndex),
                    function ($attr, $value, $fail) use ($options, $combinationValues, $combinationIndex, $messages) {
                        if (
                            !is_array($combinationValues)
                            || count($options) !== count($combinationValues)
                            || !empty(array_diff(array_keys($combinationValues), array_keys($options)))
                        ) {
                            $fail(sprintf($messages->get('combinations.record.malformed'), $combinationIndex));
                        }
                    },
                    function ($attr, $value, $fail) use ($combinationValues, $combinationIndex, $optionsMap, $messages) {
                        if (!is_array($combinationValues)) {
                            return;
                        }

                        foreach ($combinationValues as $key => $value) {
                            $isFullGroup = is_array($value);
                            if (
                                !isset($optionsMap[$key])
                                || (!$isFullGroup && !in_array($value, $optionsMap[$key]))
                                || ($isFullGroup && !empty(array_diff($optionsMap[$key], array_values($value))))
                            ) {
                                $fail(sprintf($messages->get('combinations.record.values.malformed'), $combinationIndex));
                            }
                        }
                    },
                ),
            );

            $minPrice = static::MIN_PRICE;
            $maxPrice = static::MAX_PRICE;
            $rules[] = array(
                'field' => "item:combinations:{$combinationsKey}.price",
                'rules' => array(
                    'required'         => sprintf($messages->get('combinations.record.price.required'), $combinationIndex),
                    "min[{$minPrice}]" => sprintf($messages->get('combinations.record.price.min'), $combinationIndex, $minPrice),
                    "max[{$maxPrice}]" => sprintf($messages->get('combinations.record.price.max'), $combinationIndex, $maxPrice),
                ),
            );
        }

        return $rules;
    }

    /**
     * Returns the hashes of the combinations.
     */
    private function getCombinationHashes(array $combinations = array()): array
    {
        return array_map(function ($combination) {
            if (isset($combination['combination']) && is_array($combination['combination'])) {
                ksort($combination['combination']);
                foreach ($combination['combination'] as &$combinationValue) {
                    if (is_array($combinationValue)) {
                        ksort($combinationValue);
                    }
                }
            }

            return hash('sha256', \json_encode($combination));
        }, $combinations);
    }
}
