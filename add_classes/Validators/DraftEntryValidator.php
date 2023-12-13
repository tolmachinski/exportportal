<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\IndexAwareValidatorTrait;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\IndexAwareValidatorInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_validator;

final class DraftEntryValidator extends ItemValidator implements IndexAwareValidatorInterface
{
    use IndexAwareValidatorTrait;

    /**
     * Creates the instance of validator.
     */
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        parent::__construct($validatorAdapter, null, false, $messages, $labels, $fields);
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
            'title'           => array(
                'indexed_label' => $labels->get('title.indexed'),
                'label'         => $labels->get('title'),
                'field'         => $fields->get('title'),
                'rules'         => $this->getTitleRules(static::MIN_TITLE_LENGTH, static::MAX_TITLE_LENGTH, $messages),
            ),
            'price'           => array(
                'indexed_label' => $labels->get('price.indexed'),
                'label'         => $labels->get('price'),
                'field'         => $fields->get('price'),
                'rules'         => $this->getPriceRules(static::MIN_PRICE, static::MAX_PRICE, $messages),
            ),
            'finalPrice'      => array(
                'indexed_label' => $labels->get('finalPrice.indexed'),
                'label'         => $labels->get('finalPrice'),
                'field'         => $fields->get('finalPrice'),
                'rules'         => $this->getFinalPriceRules($messages, $fields, $labels),
            ),
            'quantity'        => array(
                'indexed_label' => $labels->get('quantity.indexed'),
                'label'         => $labels->get('quantity'),
                'field'         => $fields->get('quantity'),
                'rules'         => $this->getQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages),
            ),
            'minSaleQuantity' => array(
                'indexed_label' => $labels->get('minSaleQuantity.indexed'),
                'label'         => $labels->get('minSaleQuantity'),
                'field'         => $fields->get('minSaleQuantity'),
                'rules'         => $this->getMinSaleQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages, $fields, $labels),
            ),
            'maxSaleQuantity' => array(
                'indexed_label' => $labels->get('maxSaleQuantity.indexed'),
                'label'         => $labels->get('maxSaleQuantity'),
                'field'         => $fields->get('maxSaleQuantity'),
                'rules'         => $this->getMaxSaleQuantityRules(static::MIN_QUANTITY, static::MAX_QUANTITY, $messages, $fields, $labels),
            ),
            'weight'          => array(
                'indexed_label' => $labels->get('weight.indexed'),
                'label'         => $labels->get('weight'),
                'field'         => $fields->get('weight'),
                'rules'         => $this->getWeightRules(static::MIN_WEIGHT, static::MAX_WEIGHT, $messages),
            ),
            'sizes'           => array(
                'indexed_label' => $labels->get('sizes.indexed'),
                'label'         => $labels->get('sizes'),
                'field'         => $fields->get('sizes'),
                'rules'         => $this->getSizesRules(static::MIN_DIMENSION, static::MAX_DIMENSION, $messages),
            ),
            'video'           => array(
                'indexed_label' => $labels->get('video.indexed'),
                'label'         => $labels->get('video'),
                'field'         => $fields->get('video'),
                'rules'         => $this->getVideoRules(static::MAX_VIDEO_LENGTH, $messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array_merge(
            parent::fields(),
            array(
                'title'           => 'title',
                'price'           => 'price',
                'finalPrice'      => 'discount_price',
                'quantity'        => 'quantity',
                'minSaleQuantity' => 'min_sale_quantity',
                'maxSaleQuantity' => 'max_sale_quantity',
                'weight'          => 'weight',
                'sizes'           => 'sizes',
                'video'           => 'video',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array_merge(
            parent::labels(),
            array(
                'title'                   => 'Title',
                'price'                   => 'Price',
                'sizes'                   => 'Sizes, (cm) LxWxH',
                'video'                   => 'Video',
                'weight'                  => 'Weight',
                'quantity'                => 'Quantity',
                'finalPrice'              => 'Discount Price',
                'minSaleQuantity'         => 'Minimal sale quantity',
                'maxSaleQuantity'         => 'Maximal sale quantity',
                'title.indexed'           => 'Title #%s',
                'price.indexed'           => 'Price #%s',
                'sizes.indexed'           => 'Sizes, (cm) LxWxH #%s',
                'video.indexed'           => 'Video #%s',
                'weight.indexed'          => 'Weight #%s',
                'quantity.indexed'        => 'Quantity #%s',
                'finalPrice.indexed'      => 'Discount Price #%s',
                'minSaleQuantity.indexed' => 'Minimal sale quantity #%s',
                'maxSaleQuantity.indexed' => 'Maximal sale quantity #%s',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array_merge(
            parent::messages(),
            array(
                'sizes.valid' => 'Field "%s" contains one or more invalid dimensions.',
            )
        );
    }

    protected function getSizesRules($minDimension, $maxDimension, ParameterBag $messages): array
    {
        return array(
            'required'   => $messages->get('sizes.required') ?? '',
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($minDimension, $maxDimension, $messages) {
                if (empty($value)) {
                    return;
                }

                list($length, $width, $height) = explode('x', (string) $value);
                foreach (array($length, $width, $height) as $dimension) {
                    $dimension = trim($dimension);
                    if (
                        !$validator->is_number($dimension)
                        || !$validator->float($dimension)
                        || !$validator->min($dimension, $minDimension)
                        || !$validator->max($dimension, $maxDimension)
                    ) {
                        $fail(sprintf(
                            str_replace(
                                '%d',
                                '%s',
                                $messages->get('sizes.valid') ?? $validator->get_rule_message('item_sizes')
                            ),
                            $attr,
                        ));

                        return;
                    }
                }
            },
        );
    }
}
