<?php

namespace App\Payments\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\NestedDenormalizerTrait;
use App\Common\Serializer\PropertyNormalizer;
use App\Common\Serializer\SerializerAdapterInterface;
use Money\Currency;

final class MoneyContext extends AbstractContext
{
    use NestedDenormalizerTrait;

    /**
     * The currency serializer.
     *
     * @var \App\Common\Serializer\SerializerAdapterInterface
     */
    private $currencySerializer;

    public function __construct(SerializerAdapterInterface $currencySerializer)
    {
        $this->currencySerializer = $currencySerializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array(
            PropertyNormalizer::DENORMALIZATION_CALLBACKS => array(
                'currency' => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->denormalizeNestedObject($this->currencySerializer, $innerObject, Currency::class, $format);
                },
            ),
        );
    }
}
