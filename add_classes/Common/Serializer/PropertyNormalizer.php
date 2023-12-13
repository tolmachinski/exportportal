<?php

namespace App\Common\Serializer;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BaseNormalizer;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;

class PropertyNormalizer extends BaseNormalizer
{
    const NORMALIZATION_CALLBACKS = 'normalization_callbacks';

    const DENORMALIZATION_CALLBACKS = 'denormalization_callbacks';

    const EXCLUDE_FROM_CACHE_KEY = 'exclude_from_cache_key';

    /**
     * Class discrmininator resolver.
     *
     * @var \Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface
     */
    protected $classDiscriminatorResolver;

    /**
     * Class instance instantiator.
     *
     * @var \Doctrine\Instantiator\InstantiatorInterface
     */
    private $instantiator;

    /**
     * Cache for discriminator.
     *
     * @var array
     */
    private $discriminatorCache = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        InstantiatorInterface $instantiator = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver);

        if (null === $instantiator) {
            $this->instantiator = new Instantiator();
        }

        $this->defaultContext[self::EXCLUDE_FROM_CACHE_KEY] = array(
            self::NORMALIZATION_CALLBACKS,
            self::DENORMALIZATION_CALLBACKS,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        if (isset($context[self::NORMALIZATION_CALLBACKS])) {
            if (!\is_array($context[self::NORMALIZATION_CALLBACKS])) {
                throw new InvalidArgumentException(sprintf('The "%s" context option must be an array of callables.', self::NORMALIZATION_CALLBACKS));
            }
            foreach ($context[self::NORMALIZATION_CALLBACKS] as $attribute => $callback) {
                if (!\is_callable($callback)) {
                    throw new InvalidArgumentException(sprintf('Invalid callback found for attribute "%s" in the "%s" context option.', $attribute, self::NORMALIZATION_CALLBACKS));
                }
            }
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        if (isset($context[self::DENORMALIZATION_CALLBACKS])) {
            if (!\is_array($context[self::DENORMALIZATION_CALLBACKS])) {
                throw new InvalidArgumentException(sprintf('The "%s" context option must be an array of callables.', self::DENORMALIZATION_CALLBACKS));
            }
            foreach ($context[self::DENORMALIZATION_CALLBACKS] as $attribute => $callback) {
                if (!\is_callable($callback)) {
                    throw new InvalidArgumentException(sprintf('Invalid callback found for attribute "%s" in the "%s" context option.', $attribute, self::DENORMALIZATION_CALLBACKS));
                }
            }
        }

        return parent::denormalize($data, $this->getMappedClassNameFromData($data, $class), $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            (
                \class_exists($type)
                || (
                    \interface_exists($type, false)
                    && $this->classDiscriminatorResolver
                    && null !== $this->getMappingForClassOrObject($type)
                )
            )
            || parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateObject(array &$data, $class, array &$context, \ReflectionClass $reflectionClass, $allowedAttributes, $format = null)
    {
        if (null !== $object = $this->extractObjectToPopulate($class, $context, static::OBJECT_TO_POPULATE)) {
            unset($context[static::OBJECT_TO_POPULATE]);

            return $object;
        }

        // clean up even if no match
        unset($context[static::OBJECT_TO_POPULATE]);

        return $this->instantiator->instantiate($this->getMappedClassNameFromData($data, $class));
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeValue($object, $attribute, $format = null, array $context = array())
    {
        if (null !== ($mapping = $this->getMappingForMappedClassOrObject($object))) {
            if ($attribute === $mapping->getTypeProperty()) {
                return $mapping->getMappedObjectType($object);
            }
        }

        $value = parent::getAttributeValue($object, $attribute, $format, $context);
        if (isset($context[self::NORMALIZATION_CALLBACKS][$attribute])) {
            return \call_user_func($context[self::NORMALIZATION_CALLBACKS][$attribute], $value, $object, $attribute, $format, $context);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = array())
    {
        if (isset($context[self::DENORMALIZATION_CALLBACKS][$attribute])) {
            return parent::setAttributeValue(
                $object,
                $attribute,
                \call_user_func($context[self::DENORMALIZATION_CALLBACKS][$attribute], $value, $object, $attribute, $format, $context),
                $format,
                $context
            );
        }

        return parent::setAttributeValue($object, $attribute, $value, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedAttributes($classOrObject, array $context, $attributesAsString = false)
    {
        if (false === $allowedAttributes = parent::getAllowedAttributes($classOrObject, $context, $attributesAsString)) {
            return false;
        }

        if (null !== $this->classDiscriminatorResolver) {
            $className = \is_object($classOrObject) ? \get_class($classOrObject) : $classOrObject;
            if (null !== ($discriminatorMapping = $this->getMappingForMappedClassOrObject($className))) {
                $allowedAttributes[] = $attributesAsString ? $discriminatorMapping->getTypeProperty() : new AttributeMetadata($discriminatorMapping->getTypeProperty());
            }
            if (null !== ($discriminatorMapping = $this->getMappingForClassOrObject($className))) {
                foreach ($discriminatorMapping->getTypesMapping() as $mappedClass) {
                    $allowedAttributes = array_merge($allowedAttributes, parent::getAllowedAttributes($mappedClass, $context, $attributesAsString));
                }
            }
        }

        return $allowedAttributes;
    }

    /**
     * Overwritten to update the cache key for the child.
     *
     * We must not mix up the attribute cache between parent and children.
     *
     * {@inheritdoc}
     */
    protected function createChildContext(array $parentContext, string $attribute, ?string $format): array
    {
        $context = parent::createChildContext($parentContext, $attribute, $format);
        $context['cache_key'] = $this->getCacheKey($format, $context);

        return $context;
    }

    /**
     * Returns the mapping for class.
     *
     * @param object|string $classOrObject
     *
     * @return ClassDiscriminatorMapping
     */
    private function getMappingForClassOrObject($classOrObject)
    {
        $cacheKey = \is_object($classOrObject) ? \get_class($classOrObject) : $classOrObject;
        if (!array_key_exists($cacheKey, $this->discriminatorCache)) {
            $this->discriminatorCache[$cacheKey] = null;
            if (null !== $this->classDiscriminatorResolver) {
                $mapping = $this->classDiscriminatorResolver->getMappingForClass($cacheKey);
                $this->discriminatorCache[$cacheKey] = null === $mapping ? null : $mapping;
            }
        }

        return $this->discriminatorCache[$cacheKey];
    }

    /**
     * Returns the mapping for class.
     *
     * @param object|string $classOrObject
     *
     * @return ClassDiscriminatorMapping
     */
    private function getMappingForMappedClassOrObject($classOrObject)
    {
        $cacheKey = \is_object($classOrObject) ? \get_class($classOrObject) : $classOrObject;
        if (!array_key_exists($cacheKey, $this->discriminatorCache)) {
            $this->discriminatorCache[$cacheKey] = null;
            if (null !== $this->classDiscriminatorResolver) {
                $mapping = $this->classDiscriminatorResolver->getMappingForMappedObject($cacheKey);
                $this->discriminatorCache[$cacheKey] = null === $mapping ? null : $mapping;
            }
        }

        return $this->discriminatorCache[$cacheKey];
    }

    /**
     * Returns mapped class name while knowing data and abstract class.
     *
     * @param mixed  $data
     * @param string $className
     */
    private function getMappedClassNameFromData($data, $className)
    {
        if (null !== $this->classDiscriminatorResolver && $mapping = $this->getMappingForClassOrObject($className)) {
            if (!isset($data[$mapping->getTypeProperty()])) {
                throw new RuntimeException(sprintf('Type property "%s" not found for the abstract object "%s"', $mapping->getTypeProperty(), $className));
            }

            $type = $data[$mapping->getTypeProperty()];
            if (null === ($mappedClass = $mapping->getClassForType($type))) {
                throw new RuntimeException(sprintf('The type "%s" has no mapped class for the abstract object "%s"', $type, $className));
            }

            return $mappedClass;
        }

        return $className;
    }

    /**
     * Builds the cache key for the attributes cache.
     *
     * The key must be different for every option in the context that could change which attributes should be handled.
     *
     * @param null|string $format
     * @param array       $context
     *
     * @return bool|string
     */
    private function getCacheKey($format, array $context)
    {
        $exluded = array();
        if (isset($context[self::EXCLUDE_FROM_CACHE_KEY])) {
            $exluded = $context[self::EXCLUDE_FROM_CACHE_KEY];
        } elseif (isset($this->defaultContext[self::EXCLUDE_FROM_CACHE_KEY])) {
            $exluded = $this->defaultContext[self::EXCLUDE_FROM_CACHE_KEY];
        }

        foreach ($exluded as $key) {
            unset($context[$key]);
        }

        unset($context[self::EXCLUDE_FROM_CACHE_KEY], $context['cache_key']);
        // avoid artificially different keys

        try {
            return md5($format . serialize(array(
                'context'   => $context,
                'ignored'   => $this->ignoredAttributes,
                'camelized' => $this->camelizedAttributes,
            )));
        } catch (\Exception $exception) {
            // The context cannot be serialized, skip the cache
            return false;
        }
    }
}
