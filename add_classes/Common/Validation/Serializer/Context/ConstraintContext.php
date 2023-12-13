<?php

namespace App\Common\Validation\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\NestedDenormalizerTrait;
use App\Common\Serializer\SerializerAdapterInterface;
use Money\Money;

final class ConstraintContext extends AbstractContext
{
    use NestedDenormalizerTrait;

    /**
     * The money serializer.
     *
     * @var \App\Common\Serializer\SerializerAdapterInterface
     */
    private $moneySerializer;

    public function __construct(SerializerAdapterInterface $moneySerializer)
    {
        $this->moneySerializer = $moneySerializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array();
    }
}
