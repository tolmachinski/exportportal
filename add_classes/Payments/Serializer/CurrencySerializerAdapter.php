<?php

namespace App\Payments\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\PropertyNormalizer;
use App\Payments\Serializer\Context\CurrencyContext;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class CurrencySerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultSerializer()
    {
        return new Serializer(
            array(new PropertyNormalizer(null, null, null, $this->classDiscriminatorResolver)),
            array(new JsonEncoder())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultContext()
    {
        return new CurrencyContext();
    }
}
