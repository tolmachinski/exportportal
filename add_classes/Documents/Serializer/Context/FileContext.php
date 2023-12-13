<?php

namespace App\Documents\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\PropertyNormalizer;
use App\Documents\File\FileInterface;
use DateTimeImmutable;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

final class FileContext extends AbstractContext
{
    /**
     * The UUID factory.
     *
     * @var UuidFactoryInterface
     */
    private $uuidFactory;

    public function __construct(UuidFactoryInterface $uuidFactory = null)
    {
        if (null === $uuidFactory) {
            $uuidFactory = new UuidFactory();
        }

        $this->uuidFactory = $uuidFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array(
            PropertyNormalizer::DENORMALIZATION_CALLBACKS => array(
                'id'         => function ($innerObject) { return is_string($innerObject) ? $this->uuidFactory->fromString($innerObject) : null; },
                'originalId' => function ($innerObject) { return is_string($innerObject) ? $this->uuidFactory->fromString($innerObject) : null; },
                'uploadDate' => function ($innerObject) {
                    return is_string($innerObject) ? DateTimeImmutable::createFromFormat(FileInterface::DATE_FORMAT, $innerObject) : null;
                },
            ),
            PropertyNormalizer::NORMALIZATION_CALLBACKS => array(
                'id'         => function ($innerObject) { return $innerObject instanceof UuidInterface ? $innerObject->toString() : null; },
                'originalId' => function ($innerObject) { return $innerObject instanceof UuidInterface ? $innerObject->toString() : null; },
                'uploadDate' => function ($innerObject) {
                    return $innerObject instanceof DateTimeImmutable ? $innerObject->format(FileInterface::DATE_FORMAT) : null;
                },
            ),
        );
    }
}
