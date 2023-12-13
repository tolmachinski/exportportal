<?php

namespace App\Documents\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Context\AggregatedContext;
use App\Common\Serializer\Context\ContextInterface;
use App\Common\Serializer\Mapping\ClassDiscriminator;
use App\Common\Serializer\Mapping\ClassDiscriminatorEntityInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Common\Serializer\SerializerAdapterInterface;
use App\Documents\Serializer\Context\AcceptedVersionContext;
use App\Documents\Serializer\Context\ExpiringVersionContext;
use App\Documents\Serializer\Context\RejectedVersionContext;
use App\Documents\Serializer\Context\ReplaceableVersionContext;
use App\Documents\Serializer\Context\VersionContext;
use App\Documents\Versioning\AbstractVersion;
use App\Documents\Versioning\AcceptedVersion;
use App\Documents\Versioning\PendingVersion;
use App\Documents\Versioning\RejectedVersion;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;
use App\Documents\Versioning\VersionList;
use App\Documents\Versioning\VersionTypesInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class VersionSerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * The version' user serializer.
     *
     * @var SerializerAdapterInterface
     */
    private $userSerializer;

    /**
     * The file serializer.
     *
     * @var SerializerAdapterInterface
     */
    private $fileSerializer;

    /**
     * The attachments serializer.
     *
     * @var SerializerAdapterInterface
     */
    private $attachmentsSerializer;

    public function __construct(
        SerializerInterface $serializer = null,
        ContextInterface $context = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        SerializerInterface $userSerializer = null,
        SerializerInterface $fileSerializer = null,
        SerializerInterface $attachmentsSerializer = null
    ) {
        if (null === $userSerializer) {
            $userSerializer = new UserSerializerAdapter(null, null, $classDiscriminatorResolver);
        }
        $this->userSerializer = $userSerializer;

        if (null === $fileSerializer) {
            $fileSerializer = new FileSerializerAdapter(null, null, $classDiscriminatorResolver);
        }
        $this->fileSerializer = $fileSerializer;

        if (null === $attachmentsSerializer) {
            $attachmentsSerializer = new AttachmentSerializerAdapter(null, null, $classDiscriminatorResolver);
        }
        $this->attachmentsSerializer = $attachmentsSerializer;

        parent::__construct($serializer, $context, $classDiscriminatorResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = array())
    {
        $deserialized = parent::deserialize($data, $type, $format, $context);

        if (
            false !== strpos($type, '[]')
            || VersionList::class === $type
            || is_a($type, VersionCollectionInterface::class)
            || is_a($type, Collection::class)
        ) {
            return new VersionList((array) $deserialized);
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
            new VersionContext($this->userSerializer, $this->fileSerializer, $this->attachmentsSerializer),
            new AcceptedVersionContext(),
            new RejectedVersionContext(),
            new ExpiringVersionContext(),
            new ReplaceableVersionContext(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        $mapping = new ClassDiscriminatorMapping(ClassDiscriminatorEntityInterface::DISCRIMINATOR_KEY, array(
            VersionTypesInterface::PENDING  => PendingVersion::class,
            VersionTypesInterface::ACCEPTED => AcceptedVersion::class,
            VersionTypesInterface::REJECTED => RejectedVersion::class,
        ));

        return new ClassDiscriminator(array(
            VersionInterface::class => $mapping,
            AbstractVersion::class  => $mapping,
        ));
    }
}
