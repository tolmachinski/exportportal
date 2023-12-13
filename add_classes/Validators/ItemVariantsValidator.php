<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Common\Collections\ArrayCollection;
use TinyMVC_Library_validator;
use Items_Variants_Model;
use Items_Variants_Properties_Model;
use Items_Variants_Properties_Options_Model;

final class ItemVariantsValidator extends Validator
{
    public const VALIDATE_PROPERTIES = 'validateProperties';

    public const VALIDATE_VARIANTS = 'validateVariants';

    public const VALIDATE_BOTH = 'validateAll';

    protected const MIN_PRICE = 0.01;

    protected const MAX_PRICE = 9_999_999.99;

    protected const MAX_QUANTITY = 1_000_000_000;

    /** @var int|null $itemId */
    protected $itemId;

    /** @var string $validationScheme */
    protected $validationScheme;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?int $itemId = null,
        string $validationScheme = self::VALIDATE_BOTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->itemId = $itemId;
        $this->validationScheme = $validationScheme;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * Validates the provided set of data.
     *
     * @param mixed $validationData
     */
    public function validate($validationData): bool
    {
        if (is_array($validationData)) {
            $validationData = new FlatValidationData($validationData);
        }

        $properties = $validationData->get($this->fields()['properties']);
        $combinations = $validationData->get($this->fields()['combinations']);

        if (is_array($properties)) {
            $validationData->set($this->fields()['properties'], new ArrayCollection($properties));
        }

        if (is_array($combinations)) {
            $validationData->set($this->fields()['combinations'], new ArrayCollection($combinations));
        }

        if (is_a($properties, NestedValidationData::class)) {
            $properties = iterator_to_array($properties->getIterator());

            foreach ($properties as &$property) {
                if (is_a($property, NestedValidationData::class)) {
                    $property = iterator_to_array($property->getIterator());
                }

                if (is_a($property['options'], NestedValidationData::class)) {
                    $property['options'] = iterator_to_array($property['options']->getIterator());
                }

                foreach ((array) $property['options'] as $key => $option) {
                    if (is_a($option, NestedValidationData::class)) {
                        $property['options'][$key] = iterator_to_array($option->getIterator());
                    }
                }
            }

            $validationData->set($this->fields()['properties'], new ArrayCollection($properties));
        }

        return parent::validate($validationData);
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'combinations'  => 'combinations',
            'properties'    => 'properties',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'combinations'  => 'Combination of Variations',
            'properties'    => 'Variation type with Options',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'properties.count.exceeded'                 => translate('systmess_validation_item_properties_count_exceeded'),
            'properties.name.length.exceeded'           => translate('systmess_validation_item_variant_property_name_too_long'),
            'properties.options.count.exceeded'         => translate('systmess_validation_item_property_options_count_exceeded'),
            'properties.options.name.length.exceeded'   => translate('systmess_validation_item_variant_property_option_name_too_long'),
            'variants.image.invalidImage'               => translate('systmess_validation_item_variant_image_invalid'),
            'variants.properties.notAll'                => translate('systmess_validation_item_variant_not_all_properties'),
            'variants.properties.duplicateOptions'      => translate('systmess_validation_item_variant_duplicate_options'),
            'variants.existDuplicates'                  => translate('systmess_validation_item_variants_duplicates'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'properties'    => [
                'field' => $fields->get('properties'),
                'label' => $labels->get('properties'),
                'rules' => $this->getPropertiesRules($this->itemId, $messages),
            ],
            'combinations'  => [
                'field' => $fields->get('combinations'),
                'label' => $labels->get('combinations'),
                'rules' => $this->getVariantsRules($this->itemId, static::MIN_PRICE, static::MAX_PRICE, static::MAX_QUANTITY, $messages, $fields, $labels),
            ],
        ];
    }

    protected function getPropertiesRules(?int $itemId, ParameterBag $messages): array
    {
        return !in_array($this->validationScheme, [self::VALIDATE_BOTH, self::VALIDATE_PROPERTIES]) ? [] : [
            'required'  => '',
            function (string $attr, $itemProperties, callable $fail, TinyMVC_Library_validator $validator) use ($itemId, $messages) {
                //check properties if they not empty
                if (!is_a($itemProperties, ArrayCollection::class) || $itemProperties->isEmpty()) {
                    $fail(sprintf($validator->get_rule_message('required'), $attr));

                    return;
                }

                $maxPropertiesAllowed = (int) config('item_max_variation_values', 5);

                //check properties if their count does not exceed the maximum allowable number
                if ($itemProperties > $maxPropertiesAllowed) {
                    $fail(str_replace('{{ITEM_PROPERTIES_LIMIT}}', (string) $maxPropertiesAllowed, $messages->get('properties.count.exceeded')));

                    return;
                }

                $maxPropertyOptionsAllowed = (int) config('item_max_variation_option', 5);
                $maxOptionLength = (int) config('item_max_variation_option_characters', 30);
                $maxPropertyLength = (int) config('item_variant_property_max_length', 255);

                $propertyIds = [];
                $optionsIds = [];
                foreach ($itemProperties as $propertyId => $property) {
                    //check property data integrity
                    if (
                        empty($property['id'])
                        || empty($property['name'])
                        || !in_array($property['type'] ?? '', ['exist', 'new'])
                        || empty($property['options'])
                        || !is_array($property['options'])
                    ) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }

                    if ('exist' === $property['type']) {
                        $propertyIds[] = (int) $propertyId;
                    }

                    //check property name length
                    if (!$validator->max_len((string) $property['name'], $maxPropertyLength)) {
                        $fail(str_replace('{{COUNT_CHARACTERS}}', (string) $maxPropertyLength, $messages->get('properties.name.length.exceeded')));

                        return;
                    }

                    if (count($property['options']) > $maxPropertyOptionsAllowed) {
                        $fail(str_replace('{{COUNT_OPTIONS}}', (string) $maxPropertyOptionsAllowed, $messages->get('properties.options.count.exceeded')));

                        return;
                    }

                    //validate property options
                    foreach ($property['options'] as $propertyOption) {
                        //check if all option data exists
                        if (empty($propertyOption['id']) || empty($propertyOption['name'])) {
                            $fail(translate('systmess_error_invalid_data'));

                            return;
                        }

                        //already exist options can be only for already exist property. If property is new, then all options also are new
                        if ('exist' === $property['type']) {
                            $optionsIds[] = (int) $propertyOption['id'];
                        }

                        //check option name length
                        if (!$validator->max_len((string) $propertyOption['name'], $maxOptionLength)) {
                            $fail(str_replace('{{COUNT_CHARACTERS}}', (string) $maxOptionLength, $messages->get('properties.options.name.length.exceeded')));

                            return;
                        }
                    }
                }

                //validate propeties ids and their options ids
                if (!empty($itemId) && !empty($propertyIds)) {
                    /** @var Items_Variants_Properties_Model $itemsVariantsPropertiesModel */
                    $itemsVariantsPropertiesModel = model(Items_Variants_Properties_Model::class);

                    if (count($propertyIds) != $itemsVariantsPropertiesModel->countAllBy([
                        'conditions'    => [
                            'itemId'    => $itemId,
                            'ids'       => $propertyIds
                        ],
                    ])) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }

                    /** @var Items_Variants_Properties_Options_Model $itemsVariantsPropertiesOptionsModel */
                    $itemsVariantsPropertiesOptionsModel = model(Items_Variants_Properties_Options_Model::class);

                    if (count($optionsIds) != $itemsVariantsPropertiesOptionsModel->countAllBy([
                        'conditions'    => [
                            'propertyIds'   => $propertyIds,
                            'ids'           => $optionsIds
                        ],
                    ])) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }
                }
            }
        ];
    }

    protected function getVariantsRules(?int $itemId, float $minPrice, float $maxPrice, int $maxQuantity, ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        return !in_array($this->validationScheme, [self::VALIDATE_BOTH, self::VALIDATE_VARIANTS]) ? [] : [
            'required'  => '',
            function (string $attr, $itemVariants, callable $fail, TinyMVC_Library_validator $validator) use ($itemId, $minPrice, $maxPrice, $maxQuantity, $messages, $fields, $labels) {
                //check variants if they not empty
                if (!is_a($itemVariants, ArrayCollection::class) || $itemVariants->isEmpty()) {
                    $fail(sprintf($validator->get_rule_message('required'), $attr));

                    return;
                }

                //region prepare variant properties for validation variants
                /** @var ArrayCollection $itemProperties */
                $itemProperties = $this->getValidationData()->get($fields->get('properties'));

                if (!is_a($itemProperties, ArrayCollection::class) || $itemProperties->isEmpty()) {
                    $fail(sprintf($validator->get_rule_message('required'), $labels->get('properties')));

                    return;
                }

                $countProperties = $itemProperties->count();
                //endregion prepare variant properties for validation variants

                //region prepare gallery images for validation of variant images
                $galleryImages = [];

                $newGalleryImages = $this->getValidationData()->get('images') ?: [];

                foreach ($newGalleryImages as $newGalleryImage) {
                    $galleryImages[] = pathinfo((string) $newGalleryImage, PATHINFO_BASENAME);
                }

                if (null !== $itemId) {
                    $previouslyUploadedGalleryImages = (array) $this->getValidationData()->get('images_validate');
                    if (!empty($previouslyUploadedGalleryImages)) {
                        array_push($galleryImages, ...$previouslyUploadedGalleryImages);
                    }
                }
                //endregion prepare gallery images for validation of variant images

                $variants = [];
                $variantIds = [];
                foreach ($itemVariants as $variantId => $variant) {
                    //check variant data integrity
                    if (
                        !isset($variant['id'])
                        || !in_array($variant['type'], ['new', 'exist'])
                        || !isset($variant['img'])
                        || !isset($variant['price'])
                        || !isset($variant['final_price'])
                        || !isset($variant['quantity'])
                        || !isset($variant['variants'])
                        || !is_array($variant['variants'])
                    ) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }

                    if ('exist' === $variant['type']) {
                        $variantIds[] = $variant['id'];
                    }

                    //region validate variant price
                    if (!$validator->positive_number($variant['price'])) {
                        $fail(sprintf($validator->get_rule_message('positive_number'), 'Price for combination'));

                        return;
                    }

                    if (!$validator->min($variant['price'], $minPrice)) {
                        $fail(sprintf($validator->get_rule_message('min'), 'Price for combination', $minPrice));

                        return;
                    }

                    if (!$validator->max($variant['price'], $maxPrice)) {
                        $fail(sprintf($validator->get_rule_message('max'), 'Price for combination', $maxPrice));

                        return;
                    }
                    //endregion validate variant price

                    //region validate variant final_price
                    if (!empty($variant['final_price'])) {
                        if (!$validator->positive_number($variant['final_price'])) {
                            $fail(sprintf($validator->get_rule_message('positive_number'), 'Discount price for combination'));

                            return;
                        }

                        //check if discount price is less than start price
                        if (!$validator->max($variant['final_price'], $variant['price'])) {
                            $fail(sprintf($validator->get_rule_message('max'), 'Discount price for combination', $maxPrice));

                            return;
                        }
                    }
                    //endregion validate variant final_price

                    //region validate variant quantity
                    if (empty($variant['quantity'])) {
                        $fail(sprintf($validator->get_rule_message('required'), 'Combination quantity'));

                        return;
                    }

                    if (!ctype_digit((string) $variant['quantity'])) {
                        $fail('The combination quantity is not a number');

                        return;
                    }

                    if (!$validator->max($variant['quantity'], $maxQuantity)) {
                        $fail(sprintf($validator->get_rule_message('max'), 'Combination quantity', $maxQuantity));

                        return;
                    }
                    //endregion validate variant quantity

                    //region validate variant image
                    if ('main' !== $variant['img'] && !in_array($variant['img'], $galleryImages)) {
                        $fail($messages->get('variants.image.invalidImage'));

                        return;
                    }
                    //endregion validate variant image

                    //region validate variant properties
                    if (empty($variant['variants'])) {
                        $fail('The variation options are required for each combination.');

                        return;
                    }

                    $variantOptionKeys = [];
                    $uniqueOptions = [];
                    foreach ((array) $variant['variants'] as $row) {
                        //check if each option and each property exist
                        if (!isset($itemProperties[$row['property_id']]) || !isset($itemProperties[$row['property_id']]['options'][$row['option_id']])) {
                            $fail(translate('systmess_error_invalid_data'));

                            return;
                        }

                        $optionKey = $row['property_id'] . '_' . $row['option_id'];

                        //check if variant doesn't have repeatable options
                        if (isset($uniqueOptions[$optionKey])) {
                            $fail($messages->get('variants.properties.duplicateOptions'));

                            return;
                        }

                        $uniqueOptions[$optionKey] = '';
                        $variantOptionKeys[$row['property_id']][] = $row['property_id'] . '_' . $row['option_id'];
                    }

                    //check if the variant has at least one option from each property
                    if (count(array_column((array) $variant['variants'], 'property_id', 'property_id')) != $countProperties) {
                        $fail($messages->get('variants.properties.notAll'));

                        return;
                    }
                    //endregion validate variant properties

                    //Further, all possible combinations will be formed from the options, which will be compared as strings
                    //Therefore, it is very important that the order of properties is the same in all variants
                    //So that the combination 'property1_option1 X property2_option2' does not turn out, which as a string is not equal to 'property2_option2 X property1_option1'
                    ksort($variantOptionKeys);

                    $variants[] = $variantOptionKeys;
                }

                //region check if no repeatable variants
                //make all distinct combinations
                $allCombinations = [];
                foreach ($variants as $variant) {
                    $variantCombinations = array_shift($variant);

                    while (!empty($currentPropertyOptions = array_shift($variant))) {
                        $tempCombinations = [];

                        foreach ($variantCombinations as $variantCombination) {
                            foreach ($currentPropertyOptions as $currentOption) {
                                $tempCombinations[] = "{$variantCombination}__{$currentOption}";
                            }
                        }

                        $variantCombinations = $tempCombinations;
                    }

                    $allCombinations = array_merge($allCombinations, $variantCombinations);
                }

                if (count($allCombinations) != count(array_unique($allCombinations))) {
                    $fail($messages->get('variants.existDuplicates'));

                    return;
                }
                //endregion check if no repetable variants

                //region validate variants ids
                if (!empty($variantIds)) {
                    /** @var Items_Variants_Model $itemsVariantsModel */
                    $itemsVariantsModel = model(Items_Variants_Model::class);

                    if (count($variantIds) != $itemsVariantsModel->countAllBy([
                        'conditions' => [
                            'ids'   => $variantIds,
                        ],
                    ])) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }
                }
                //endregion validate variants ids
            },
        ];
    }
}
