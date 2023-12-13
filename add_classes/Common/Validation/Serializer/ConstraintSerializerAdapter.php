<?php

namespace App\Common\Validation\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Context\AggregatedContext;
use App\Common\Serializer\Context\ContextInterface;
use App\Common\Serializer\Mapping\ClassDiscriminator;
use App\Common\Serializer\Mapping\ClassDiscriminatorEntityInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Payments\Serializer\MoneySerializerAdapter;
use App\Common\Validation\ConstraintInterface;
use App\Common\Validation\ConstraintList;
use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\Constraints\AbstractConstraint;
use App\Common\Validation\Constraints\MaximumAmount;
use App\Common\Validation\Constraints\MinimalAmount;
use App\Common\Validation\Serializer\Context\ConstraintContext;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class ConstraintSerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * The money serializer.
     *
     * @var \App\Common\Serializer\SerializerAdapterInterface
     */
    private $moneySerializer;

    public function __construct(
        SerializerInterface $serializer = null,
        ContextInterface $context = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        SerializerInterface $moneySerializer = null
    ) {
        if (null === $moneySerializer) {
            $moneySerializer = new MoneySerializerAdapter(null, null, $classDiscriminatorResolver);
        }
        $this->moneySerializer = $moneySerializer;

        parent::__construct($serializer, $context, $classDiscriminatorResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = array())
    {
        $isList = false;
        if (
            ConstraintList::class === $type
            || ConstraintListInterface::class === $type
            || is_a($type, ConstraintListInterface::class)
        ) {
            $isList = true;
            $type = ConstraintInterface::class . '[]';
        }

        $deserialized = parent::deserialize($data, $type, $format, $context);
        if ($isList) {
            return new ConstraintList((array) $deserialized);
        }

        return $deserialized;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultSerializer()
    {
        return new Serializer(
            array(
                new ArrayDenormalizer(),
                new PropertyNormalizer(null, null, null, $this->classDiscriminatorResolver),
            ),
            array(new JsonEncoder())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultContext()
    {
        return new AggregatedContext(array(
            new ConstraintContext($this->moneySerializer),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        $mapping = new ClassDiscriminatorMapping(ClassDiscriminatorEntityInterface::DISCRIMINATOR_KEY, array(
            'maximum_amount' => MaximumAmount::class,
            'minimal_amount' => MinimalAmount::class,
        ));

        return new ClassDiscriminator(array(
            AbstractConstraint::class  => $mapping,
            ConstraintInterface::class => $mapping,
        ));
    }
}
