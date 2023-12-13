<?php

namespace App\Payments\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Context\ContextInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Payments\Serializer\Context\MoneyContext;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class MoneySerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * The currency serializer.
     *
     * @var \App\Common\Serializer\SerializerAdapterInterface
     */
    private $currencySerializer;

    public function __construct(
        SerializerInterface $serializer = null,
        ContextInterface $context = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        SerializerInterface $currencySerializer = null
    ) {
        if (null === $currencySerializer) {
            $currencySerializer = new CurrencySerializerAdapter(null, null, $classDiscriminatorResolver);
        }
        $this->currencySerializer = $currencySerializer;

        parent::__construct($serializer, $context, $classDiscriminatorResolver);
    }

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
        return new MoneyContext($this->currencySerializer);
    }
}
