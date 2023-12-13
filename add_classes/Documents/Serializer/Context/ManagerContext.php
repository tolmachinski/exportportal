<?php

namespace App\Documents\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\PropertyNormalizer;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

final class ManagerContext extends AbstractContext
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
                'uuid' => function ($innerObject) {
                    return is_string($innerObject) ? $this->uuidFactory->fromString($innerObject) : null;
                },
            ),
            PropertyNormalizer::NORMALIZATION_CALLBACKS => array(
                'uuid' => function ($innerObject) {
                    return $innerObject instanceof UuidInterface ? $innerObject->toString() : null;
                },
            ),
        );
    }
}
