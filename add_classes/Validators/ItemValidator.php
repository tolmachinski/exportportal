<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use DateTimeImmutable;
use Items_Model;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_validator;
use TinyMVC_Library_VideoThumb;

class ItemValidator extends Validator
{
    protected const MIN_TITLE_LENGTH = 4;

    protected const MAX_TITLE_LENGTH = 70;

    protected const MAX_VIDEO_LENGTH = 200;

    protected const MAX_HS_NUMBER_LENGTH = 13;

    protected const MAX_DESCRIPTION_LENGTH = 20000;

    protected const MIN_YEAR = 1;

    protected const MIN_PRICE = '0.01';

    protected const MAX_PRICE = 9_999_999.99;

    protected const MIN_WEIGHT = '0.001';

    protected const MAX_WEIGHT = '999999999999.999';

    protected const MIN_QUANTITY = 1;

    protected const MAX_QUANTITY = 1_000_000_000;

    protected const MIN_DIMENSION = '0.01';

    protected const MAX_DIMENSION = '99999.99';

    /**
     * The max year value.
     *
     * @var int
     */
    private $maxYear;

    /**
     * True, if the item is with variants
     *
     * @var bool
     */
    private $hasVariants;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?string $maxYear = null,
        ?bool $hasVariants = false,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxYear = (int) ($maxYear ?? (new DateTimeImmutable())->format('Y'));
        $this->hasVariants = (bool) $hasVariants;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return array(
            'hsTariffNumber'  => array(
                'field' => $fields->get('hsTariffNumber'),
                'label' => $labels->get('hsTariffNumber'),
                'rules' => $this->getHsTariffNumberRules(static::MAX_HS_NUMBER_LENGTH, $messages),
            ),
            'title'           => array(
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules(static::MIN_TITLE_LENGTH, static::MAX_TITLE_LENGTH, $messages),
            ),
            'price'           => array(
                'field' => $fields->get('price'),
                'label' => $labels->get('price'),
                'rules' => $this->getPriceRules(static::MIN_PRICE, static::MAX_PRICE, $messages),
            ),
            'finalPrice'      => array(
                'field' => $fields->get('finalPrice'),
                'label' => $labels->get('finalPrice'),
                'rules' => $this->getFinalPriceRules($messages, $fields, $labels),
            ),
            'quantity'        => array(
                'field' => $fields->get('quantity'),
                'label' => $labels->get('quantity'),
                'rules' => $this->getQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages),
            ),
            'length'          => array(
                'field' => $fields->get('length'),
                'label' => $labels->get('length'),
                'rules' => $this->getLengthRules(static::MIN_DIMENSION, static::MAX_DIMENSION, $messages),
            ),
            'width'           => array(
                'field' => $fields->get('width'),
                'label' => $labels->get('width'),
                'rules' => $this->getWidthRules(static::MIN_DIMENSION, static::MAX_DIMENSION, $messages),
            ),
            'height'          => array(
                'field' => $fields->get('height'),
                'label' => $labels->get('height'),
                'rules' => $this->getHeightRules(static::MIN_DIMENSION, static::MAX_DIMENSION, $messages),
            ),
            'weight'          => array(
                'field' => $fields->get('weight'),
                'label' => $labels->get('weight'),
                'rules' => $this->getWeightRules(static::MIN_WEIGHT, static::MAX_WEIGHT, $messages),
            ),
            'year'            => array(
                'field' => $fields->get('year'),
                'label' => $labels->get('year'),
                'rules' => $this->getYearRules(static::MIN_YEAR, $this->maxYear, $messages),
            ),
            'originCountry'   => array(
                'field' => $fields->get('originCountry'),
                'label' => $labels->get('originCountry'),
                'rules' => $this->getOriginCountryRules($messages),
            ),
            'tags'            => array(
                'field' => $fields->get('tags'),
                'label' => $labels->get('tags'),
                'rules' => $this->getTagRules($messages),
            ),
            'description'     => array(
                'field' => $fields->get('description'),
                'label' => $labels->get('description'),
                'rules' => $this->getDescriptionRules(static::MAX_DESCRIPTION_LENGTH, $messages),
            ),
            'video'           => array(
                'field' => $fields->get('video'),
                'label' => $labels->get('video'),
                'rules' => $this->getVideoRules(static::MAX_VIDEO_LENGTH, $messages),
            ),
            'unitType'        => array(
                'field' => $fields->get('unitType'),
                'label' => $labels->get('unitType'),
                'rules' => $this->getUnitTypeRules($messages, $fields, $labels),
            ),
            'minSaleQuantity' => array(
                'field' => $fields->get('minSaleQuantity'),
                'label' => $labels->get('minSaleQuantity'),
                'rules' => $this->getMinSaleQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages, $fields, $labels),
            ),
            'maxSaleQuantity' => array(
                'field' => $fields->get('maxSaleQuantity'),
                'label' => $labels->get('maxSaleQuantity'),
                'rules' => $this->getMaxSaleQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages, $fields, $labels),
            ),
            'outOfStock' => array(
                'field' => $fields->get('outOfStock'),
                'label' => $labels->get('outOfStock'),
                'rules' => $this->getOutOfStockRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages, $fields, $labels),
            ),
            'purchaseOptions' => array(
                'field' => $fields->get('purchaseOptions'),
                'label' => $labels->get('purchaseOptions'),
                'rules' => $this->getPurchaseOptionsRules($messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'year'              => 'year',
            'tags'              => 'tags',
            'title'             => 'title',
            'price'             => 'price',
            'video'             => 'video',
            'width'             => 'item_width',
            'length'            => 'item_length',
            'height'            => 'item_height',
            'weight'            => 'weight',
            'quantity'          => 'quantity',
            'unitType'          => 'unit_type',
            'finalPrice'        => 'discount_price',
            'description'       => 'description',
            'originCountry'     => 'origin_country',
            'purchaseOptions'   => 'purchase_options',
            'minSaleQuantity'   => 'min_sale_quantity',
            'maxSaleQuantity'   => 'max_sale_quantity',
            'hsTariffNumber'    => 'hs_tariff_number',
            'combinations'      => 'combinations',
            'outOfStock'        => 'out_of_stock_quantity'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'year'              => 'Year',
            'tags'              => 'Tags',
            'title'             => 'Title',
            'price'             => 'Price',
            'video'             => 'Video',
            'width'             => 'Width',
            'length'            => 'Length',
            'height'            => 'Height',
            'weight'            => 'Weight',
            'quantity'          => 'Quantity',
            'unitType'          => 'Unit type',
            'finalPrice'        => 'Discount Price',
            'description'       => 'Description',
            'originCountry'     => 'Country of origin',
            'purchaseOptions'   => 'Purchase options',
            'minSaleQuantity'   => 'Minimal sale quantity',
            'maxSaleQuantity'   => 'Maximal sale quantity',
            'hsTariffNumber'    => 'Harmonized Tariff Schedule',
            'outOfStock'        => 'Out of Stock',
            'combinations'      => 'Combinations',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'video.valid'                   => translate('validation_item_video'),
            'tags.atLeastOneValid'          => translate('validation_item_tags_at_least_one_valid'),
            'tags.notAllAreValid'           => translate('validation_item_tags_not_all_are_valid'),
            'finalPrice.notEmpty'           => translate('validation_item_final_price_be_greater_than_zero'),
            'uniType.notValid'              => translate('validation_item_unit_type_not_valid'),
            'outOfStock.minAmount'          => translate('validation_item_unit_type_not_valid'),
        ];
    }

    /**
     * Get the HsTariffNumber validation rule.
     */
    protected function getHsTariffNumberRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'              => $messages->get('hsTariffNumber.required') ?? '',
            'hs_tarif_number'       => $messages->get('hsTariffNumber.valid') ?? '',
            "max_len[{$maxLength}]" => $messages->get('hsTariffNumber.maxLength') ?? '',
        );
    }

    /**
     * Get the Title validation rule.
     */
    protected function getTitleRules(int $minLength, int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'              => $messages->get('title.required') ?? '',
            'valide_title'          => $messages->get('title.valid') ?? '',
            "min_len[{$minLength}]" => $messages->get('title.minLength') ?? '',
            "max_len[{$maxLength}]" => $messages->get('title.maxLength') ?? '',
        );
    }

    /**
     * Get the Price validation rule.
     *
     * @param mixed $minPrice
     * @param mixed $maxPrice
     */
    protected function getPriceRules($minPrice, $maxPrice, ParameterBag $messages): array
    {
        return $this->hasVariants ? [] : [
            'required'          => $messages->get('price.required') ?? '',
            'is_number'         => $messages->get('price.number') ?? '',
            'positive_number'   => $messages->get('price.valid') ?? '',
            "min[{$minPrice}]"  => $messages->get('price.min') ?? '',
            "max[{$maxPrice}]"  => $messages->get('price.max') ?? '',
        ];
    }

    /**
     * Get the FinalPrice validation rule.
     */
    protected function getFinalPriceRules(ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        if ($this->hasVariants) {
            return [];
        }

        $priceField = $fields->get('price') ?? 'price';

        return array(
            'is_number'       => $messages->get('finalPrice.number') ?? '',
            'positive_number' => $messages->get('finalPrice.valid') ?? '',
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($messages, $labels, $priceField) {
                if (!$validator->less_than_or_equal_to_field($value, $priceField)) {
                    $fail(sprintf(
                        str_replace(
                            '%d',
                            '%s',
                            $messages->get('finalPrice.lessThanOrEqual') ?? $validator->get_rule_message('less_than_or_equal_to_field')
                        ),
                        $attr,
                        $labels->get('price')
                    ));
                }
            }
        );
    }

    /**
     * Get the Quantity validation rule.
     */
    protected function getQuantityRules(int $minAmount, int $maxAmount, ParameterBag $messages): array
    {
        return $this->hasVariants ? [] : [
            'required'          => $messages->get('quantity.required') ?? '',
            'is_number'         => $messages->get('quantity.natural') ?? '',
            'natural'           => $messages->get('quantity.natural') ?? '',
            "min[{$minAmount}]" => $messages->get('quantity.minAmount') ?? '',
            "max[{$maxAmount}]" => $messages->get('quantity.maxAmount') ?? '',
        ];
    }

    /**
     * Get the Length validation rule.
     *
     * @param mixed $minDimension
     * @param mixed $maxDimension
     */
    protected function getLengthRules($minDimension, $maxDimension, ParameterBag $messages): array
    {
        return array(
            'required'             => $messages->get('length.required') ?? '',
            'is_number'            => $messages->get('length.number') ?? '',
            "min[{$minDimension}]" => $messages->get('length.min') ?? '',
            "max[{$maxDimension}]" => $messages->get('length.max') ?? '',
        );
    }

    /**
     * Get the Width validation rule.
     *
     * @param mixed $minDimension
     * @param mixed $maxDimension
     */
    protected function getWidthRules($minDimension, $maxDimension, ParameterBag $messages): array
    {
        return array(
            'required'             => $messages->get('width.required') ?? '',
            'is_number'            => $messages->get('width.number') ?? '',
            "min[{$minDimension}]" => $messages->get('width.min') ?? '',
            "max[{$maxDimension}]" => $messages->get('width.max') ?? '',
        );
    }

    /**
     * Get the Height validation rule.
     *
     * @param mixed $minDimension
     * @param mixed $maxDimension
     */
    protected function getHeightRules($minDimension, $maxDimension, ParameterBag $messages): array
    {
        return array(
            'required'             => $messages->get('height.required') ?? '',
            'is_number'            => $messages->get('height.number') ?? '',
            "min[{$minDimension}]" => $messages->get('height.min') ?? '',
            "max[{$maxDimension}]" => $messages->get('height.max') ?? '',
        );
    }

    /**
     * Get the Weight validation rule.
     *
     * @param mixed $minWeight
     * @param mixed $maxWeight
     */
    protected function getWeightRules($minWeight, $maxWeight, ParameterBag $messages): array
    {
        return array(
            'required'          => $messages->get('weight.required') ?? '',
            "min[{$minWeight}]" => $messages->get('weight.min') ?? '',
            "max[{$maxWeight}]" => $messages->get('weight.max') ?? '',
        );
    }

    /**
     * Get the Year validation rule.
     */
    protected function getYearRules(int $minYear, int $maxYear, ParameterBag $messages): array
    {
        return array(
            'required'        => $messages->get('year.required') ?? '',
            'natural'         => $messages->get('year.natural') ?? '',
            "min[{$minYear}]" => $messages->get('year.min') ?? '',
            "max[{$maxYear}]" => $messages->get('year.max') ?? '',
        );
    }

    /**
     * Get the OriginCountry validation rule.
     */
    protected function getOriginCountryRules(ParameterBag $messages): array
    {
        return array(
            'required' => $messages->get('originCountry.required'),
            'natural'  => $messages->get('originCountry.natural'),
        );
    }

    /**
     * Get the OriginCountry validation rule.
     */
    protected function getTagRules(ParameterBag $messages): array
    {
        return array(
            'required' => $messages->get('tags.required'),
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                $tags = array();
                $inputTags = explode(';', $value ?? '') ?? array();
                foreach ($inputTags as $tag) {
                    $tag = \cleanInput($tag);
                    if ($validator->valid_tag($tag)) {
                        $tags[] = $tag;
                    }
                }

                if (empty($tags)) {
                    $fail(sprintf($messages->get('tags.atLeastOneValid'), $attr));
                }

                if(count($inputTags) != count($tags)){
                    $fail(sprintf($messages->get('tags.notAllAreValid')));
                }
            },
        );
    }

    /**
     * Get the Description validation rule.
     */
    protected function getDescriptionRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            "html_max_len[{$maxLength}]" => $messages->get('description.maxLength') ?? '',
        );
    }

    /**
     * Get the Video validation rule.
     */
    protected function getVideoRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'valid_url'             => $messages->get('video.valid') ?? '',
            "max_len[{$maxLength}]" => $messages->get('video.maxLenght') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (empty($value)) {
                    return;
                }

                if (empty(library(TinyMVC_Library_VideoThumb::class)->getVID($value))) {
                    $fail(sprintf($messages->get('video.valid'), $attr));
                }
            },
        );
    }

    /**
     * Get the UnitType validation rule.
     */
    protected function getUnitTypeRules(ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        return array(
            'required' => $messages->get('unitType.required') ?? '',
            'natural'  => $messages->get('unitType.natural') ?? '',
            function (string $attr, $unitTypeId, callable $fail, TinyMVC_Library_validator $validator) use ($messages, $fields, $labels) {
                /** @var Items_Model $itemsModel */
                $itemsModel = model(Items_Model::class);

                if (empty($unitType = $itemsModel->get_unit_type((int) $unitTypeId))) {
                    $fail(sprintf($messages->get('uniType.notValid') ?? '', $labels->get('unitType')));
                }
            },
        );
    }

    /**
     * Get the MinSaleQuantity validation rule.
     */
    protected function getMinSaleQuantityRules(int $minAmount, int $maxAmount, ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        return array(
            'required'          => $messages->get('minSaleQuantity.required') ?? '',
            'is_number'         => $messages->get('minSaleQuantity.integer') ?? '',
            'natural'           => $messages->get('minSaleQuantity.natural') ?? '',
            "min[{$minAmount}]" => $messages->get('minSaleQuantity.minAmount') ?? '',
            "max[{$maxAmount}]" => $messages->get('minSaleQuantity.maxAmount') ?? '',
        );
    }

    /**
     * Get the MaxSaleQuantity validation rule.
     */
    protected function getMaxSaleQuantityRules(int $minAmount, int $maxAmount, ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        return array(
            'required'          => $messages->get('maxSaleQuantity.required') ?? '',
            'is_number'         => $messages->get('maxSaleQuantity.integer') ?? '',
            'natural'           => $messages->get('maxSaleQuantity.natural') ?? '',
            "min[{$minAmount}]" => $messages->get('maxSaleQuantity.minAmount') ?? '',
            "max[{$maxAmount}]" => $messages->get('maxSaleQuantity.maxAmount') ?? '',
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($messages, $fields, $labels) {
                if (!$validator->greater_than_or_equal_to_field($value, $fields->get('minSaleQuantity'))) {
                    $fail(sprintf(
                        str_replace(
                            '%d',
                            '%s',
                            $messages->get('maxSaleQuantity.greaterThan') ?? $validator->get_rule_message('greater_than_or_equal_to_field')
                        ),
                        $attr,
                        $labels->get('minSaleQuantity')
                    ));
                }
            },
        );
    }

    /**
     * Get the OutOfStock validation rule.
     */
    protected function getOutOfStockRules(int $minAmount, int $maxAmount, ParameterBag $messages, ParameterBag $fields, ParameterBag $labels): array
    {
        return $this->hasVariants ? [] : [
            'is_number'         => $messages->get('outOfStock.integer') ?? '',
            'natural'           => $messages->get('outOfStock.natural') ?? '',
            "max[{$maxAmount}]" => $messages->get('outOfStock.maxAmount') ?? '',
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($minAmount, $messages, $labels) {
                if ('' !== $value && null !== $value && !$validator->min($value, $minAmount)) {
                    $validator->get_rule_message('min');
                    $fail(sprintf($validator->get_rule_message('min'), $attr, $minAmount));
                }
            },
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($messages, $fields, $labels) {
                if (!$validator->less_than_or_equal_to_field($value, $fields->get('quantity'))) {
                    $fail(sprintf(
                        str_replace(
                            '%d',
                            '%s',
                            $messages->get('outOfStock.less_than_or_equal_to_field') ?? $validator->get_rule_message('less_than_or_equal_to_field')
                        ),
                        $attr,
                        $labels->get('quantity')
                    ));
                }
            },
        ];
    }

    /**
     * Get the PurchaseOptions validation rule.
     */
    protected function getPurchaseOptionsRules(ParameterBag $messages): array
    {
        return array(
            'required' => $messages->get('purchaseOptions.required') ?? '',
        );
    }
}
