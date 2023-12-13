<?php

declare(strict_types=1);

namespace App\Documents\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Mapping\ClassDiscriminator;
use App\Common\Serializer\Mapping\ClassDiscriminatorEntityInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Documents\File\File;
use App\Documents\File\FileCollectionInterface;
use App\Documents\File\FileCopy;
use App\Documents\File\FileInterface;
use App\Documents\File\FileList;
use App\Documents\File\FileTypesInterface;
use App\Documents\File\SystemFile;
use App\Documents\Serializer\Context\FileContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

final class FileSerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, array $context = array())
    {
        if ($data instanceof Collection) {
            $data = $data->getValues();
        }

        return parent::serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = array())
    {
        if ($isList = $this->isListType($type)) {
            $type = FileInterface::class . '[]';
        }

        $deserialized = parent::deserialize($data, $type, $format, $context);
        if ($isList || is_array($deserialized)) {
            return new ArrayCollection($deserialized);
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
        return new FileContext();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        return new ClassDiscriminator(array(
            FileInterface::class => new ClassDiscriminatorMapping(ClassDiscriminatorEntityInterface::DISCRIMINATOR_KEY, array(
                FileTypesInterface::SYSTEM_FILE => SystemFile::class,
                FileTypesInterface::FILE_COPY   => FileCopy::class,
                FileTypesInterface::FILE        => File::class,
            )),
        ));
    }

    /**
     * Check if type is one of the allowed list types.
     */
    private function isListType(string $type): bool
    {
        $variants = array(
            'array',
            FileList::class,
            Collection::class,
            ArrayCollection::class,
            FileCollectionInterface::class,
        );

        foreach ($variants as $variant) {
            if (
                $type === $variant
                || $type instanceof $variant
                || is_a($type, $variant)
                || is_subclass_of($type, $variant)
            ) {
                return true;
            }
        }

        return false;
    }
}
