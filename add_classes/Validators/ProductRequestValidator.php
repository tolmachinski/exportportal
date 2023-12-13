<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Category_Model;
use Closure;
use Country_Model;
use Validator as LegacyValidator;

final class ProductRequestValidator extends Validator
{
    /**
     * The minimal price value.
     */
    private const MIN_PRICE = 0.01;

    /**
     * The maximum price value.
     */
    private const MAX_PRICE = 999999999999.99;

    /**
     * The minimal quantity value.
     */
    private const MIN_QUANTITY = 1;

    /**
     * The maximum quantity value.
     */
    private const MAX_QUANTITY = 99999999999;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return array(
            array(
                'field' => 'title',
                'label' => $labels->get('productTitle'),
                'rules' => array(
                    'required'     => $messages->get('title.required') ?? '',
                    'min_len[2]'   => $messages->get('title.minLength') ?? '',
                    'max_len[300]' => $messages->get('title.maxLength') ?? '',
                ),
            ),
            array(
                'field' => 'category',
                'label' => $labels->get('category'),
                'rules' => array(
                    'required' => $messages->get('category.required') ?? '',
                    function (string $attr, $value, Closure $fail) use ($messages) {
                        if ('' !== $value && null !== $value && !model(Category_Model::class)->exist_category($value)) {
                            $fail(sprintf($messages->get('category.notFound'), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => 'quantity',
                'label' => $labels->get('quantity'),
                'rules' => array(
                    'integer'          => $messages->get('quantity.int') ?? '',
                    'natural'          => $messages->get('quantity.natural') ?? '',
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->min($value, static::MIN_QUANTITY)) {
                            $fail(sprintf($messages->get('quantity.min'), $attr, static::MIN_QUANTITY));
                        }
                    },
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->max($value, static::MAX_QUANTITY)) {
                            $fail(sprintf($messages->get('quantity.max'), $attr, static::MAX_QUANTITY));
                        }
                    },
                ),
            ),
            array(
                'field' => 'start_price',
                'label' => $labels->get('startPrice'),
                'rules' => array(
                    'positive_number' => $messages->get('startPrice.positiveNumber') ?? '',
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->min($value, static::MIN_PRICE)) {
                            $fail(sprintf($messages->get('price.min'), $attr, static::MIN_PRICE));
                        }
                    },
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->max($value, static::MAX_PRICE)) {
                            $fail(sprintf($messages->get('price.max'), $attr, static::MAX_PRICE));
                        }
                    },
                ),
            ),
            array(
                'field' => 'final_price',
                'label' => $labels->get('finalPrice'),
                'rules' => array(
                    'positive_number' => $messages->get('finalPrice.positiveNumber') ?? '',
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->min($value, static::MIN_PRICE)) {
                            $fail(sprintf($messages->get('price.min'), $attr, static::MIN_PRICE));
                        }
                    },
                    function (string $attr, $value, Closure $fail, LegacyValidator $validator) use ($messages) {
                        if ('' !== $value && null !== $value && !$validator->max($value, static::MAX_PRICE)) {
                            $fail(sprintf($messages->get('price.max'), $attr, static::MAX_PRICE));
                        }
                    },
                ),
            ),
            array(
                'field' => 'departure_country',
                'label' => $labels->get('departureCountry'),
                'rules' => array(
                    'natural' => $messages->get('country.natural', ''),
                    function (string $attr, $value, Closure $fail) use ($messages) {
                        if ('' !== $value && null !== $value && !model(Country_Model::class)->has_country($value)) {
                            $fail(sprintf($messages->get('country.valid', ''), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => 'destination_country',
                'label' => $labels->get('destinationCountry'),
                'rules' => array(
                    'natural' => $messages->get('country.natural', ''),
                    function (string $attr, $value, Closure $fail) use ($messages) {
                        if (null !== $value && !model(Country_Model::class)->has_country($value)) {
                            $fail(sprintf($messages->get('country.valid', ''), $attr));
                        }
                    },
                ),
            ),
            array(
                'field' => 'details',
                'label' => $labels->get('details'),
                'rules' => array(
                    'max_len[500]' => $messages->get('details.maxLength') ?? '',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'details'            => 'Details',
            'quantity'           => 'Amount',
            'category'           => 'Category',
            'finalPrice'         => 'Price to',
            'startPrice'         => 'Price from',
            'productTitle'       => 'Product name',
            'departureCountry'   => 'Country from',
            'destinationCountry' => 'Country to',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'price.min'         => 'Field "%s" cannot contain a value less than %s.',
            'price.max'         => 'Field "%s" cannot contain a value greater than %s.',
            'quantity.min'      => 'Field "%s" cannot contain a value less than %s.',
            'quantity.max'      => 'Field "%s" cannot contain a value greater than %s.',
            'country.valid'     => 'The country does not appear to be valid. Please choose the correct one from the "%s" list.',
            'category.notFound' => 'The product category is not found. Please choose one from the list below.',
        );
    }
}
