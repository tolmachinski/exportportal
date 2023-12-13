<?php

namespace App\Common\Serializer;

use App\Common\Serializer\Context\ContextInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractSerializerAdapter implements SerializerAdapterInterface
{
    /**
     * Class discrmininator resolver.
     *
     * @var \Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface
     */
    protected $classDiscriminatorResolver;

    /**
     * The list of classes that will be used to created the default discriminator map.
     *
     * @var string[]
     */
    protected $discriminatorMappingEntities = array();

    /**
     * The root value of the default discriminator map.
     *
     * @var string
     */
    protected $discriminatorMappingRoot;

    /**
     * The internal serializer.
     *
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * The serialization context.
     *
     * @var \App\Common\Serializer\Context\ContextInterface
     */
    private $context;

    public function __construct(
        SerializerInterface $serializer = null,
        ContextInterface $context = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null
    ) {
        if (empty($this->classDiscriminatorResolver)) {
            $classDiscriminatorResolver = $this->getDefaultClassDiscriminatorResolver();
        }
        $this->classDiscriminatorResolver = $classDiscriminatorResolver;

        if (empty($serializer)) {
            $serializer = $this->getDefaultSerializer();
        }
        $this->serializer = $serializer;

        if (empty($context)) {
            $context = $this->getDefaultContext();
        }
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, array $context = array())
    {
        return $this->serializer->serialize(
            $data,
            $format,
            array_merge(
                $this->context->getContext(),
                is_array($context) ? $context : array()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = array())
    {
        return $this->serializer->deserialize(
            $data,
            $type,
            $format,
            array_merge(
                $this->context->getContext(),
                is_array($context) ? $context : array()
            )
        );
    }

    /**
     * Returns the default serializer.
     *
     * @return \Symfony\Component\Serializer\SerializerInterface
     */
    abstract protected function getDefaultSerializer();

    /**
     * Returns the default serializer context.
     *
     * @return \App\Common\Serializer\Context\ContextInterface
     */
    abstract protected function getDefaultContext();

    /**
     * Returns the deafult discriminator resolver.
     *
     * @return null|\Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        return null;
    }
}
