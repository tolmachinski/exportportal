<?php

namespace App\Documents\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\NestedDenormalizerTrait;
use App\Common\Serializer\NestedNormalizerTrait;
use App\Common\Serializer\PropertyNormalizer;
use App\Common\Serializer\SerializerAdapterInterface;
use App\Documents\Attachment\AttachmentCollectionInterface;
use App\Documents\Attachment\AttachmentInterface;
use App\Documents\Attachment\AttachmentList;
use App\Documents\File\FileInterface;
use App\Documents\User\UserInterface;
use App\Documents\Versioning\ContentContext;
use App\Documents\Versioning\VersionInterface;
use DateTimeImmutable;

final class VersionContext extends AbstractContext
{
    use NestedNormalizerTrait;
    use NestedDenormalizerTrait;

    /**
     * The user serializer.
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
        SerializerAdapterInterface $userSerializer,
        SerializerAdapterInterface $fileSerializer,
        SerializerAdapterInterface $attachmentsSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->fileSerializer = $fileSerializer;
        $this->attachmentsSerializer = $attachmentsSerializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array(
            PropertyNormalizer::DENORMALIZATION_CALLBACKS => array(
                'creationDate'   => function ($innerObject) {
                    return is_string($innerObject) ? DateTimeImmutable::createFromFormat(VersionInterface::DATE_FORMAT, $innerObject) : null;
                },
                'manager'        => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->denormalizeNestedObject($this->userSerializer, $innerObject, UserInterface::class, $format);
                },
                'file'           => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->denormalizeNestedObject($this->fileSerializer, $innerObject, FileInterface::class, $format);
                },
                'context'         => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return new ContentContext($innerObject ?? array());
                },
                'attachments'    => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return new AttachmentList((array) $this->denormalizeNestedObject(
                        $this->attachmentsSerializer,
                        $innerObject,
                        AttachmentInterface::class . '[]',
                        $format
                    ));
                },
            ),
            PropertyNormalizer::NORMALIZATION_CALLBACKS   => array(
                'creationDate'   => function ($innerObject) {
                    return $innerObject instanceof DateTimeImmutable ? $innerObject->format(VersionInterface::DATE_FORMAT) : null;
                },
                'manager'        => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->normalizeNestedObject($this->userSerializer, $innerObject, UserInterface::class, $format);
                },
                'file'           => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->normalizeNestedObject($this->fileSerializer, $innerObject, FileInterface::class, $format);
                },
                'attachments'    => function ($innerObject, $outerObject, $attributeName, $format = null, array $context = array()) {
                    return $this->normalizeNestedObject($this->attachmentsSerializer, $innerObject, AttachmentCollectionInterface::class, $format);
                },
            ),
        );
    }
}
