<?php

namespace App\Documents\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Context\AggregatedContext;
use App\Common\Serializer\Mapping\ClassDiscriminator;
use App\Common\Serializer\Mapping\ClassDiscriminatorEntityInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Documents\Attachment\AttachmentInterface;
use App\Documents\Attachment\AttachmentTypesInterface;
use App\Documents\Attachment\FileAttachment;
use App\Documents\Serializer\Context\AttachmentContext;
use App\Documents\Serializer\Context\FileContext;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

final class AttachmentSerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultSerializer()
    {
        return new Serializer(
            array(new ArrayDenormalizer(), new PropertyNormalizer(null, null, null, $this->classDiscriminatorResolver)),
            array(new JsonEncoder())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultContext()
    {
        return new AggregatedContext(array(
            new AttachmentContext(),
            new FileContext(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        return new ClassDiscriminator(array(
            AttachmentInterface::class => new ClassDiscriminatorMapping(ClassDiscriminatorEntityInterface::DISCRIMINATOR_KEY, array(
                AttachmentTypesInterface::FILE_ATTACHMENT  => FileAttachment::class,
            )),
        ));
    }
}
