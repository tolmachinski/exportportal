<?php

namespace App\Documents\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;

final class AttachmentContext extends AbstractContext
{
    /**
     * The UUID factory.
     *
     * @var \Ramsey\Uuid\UuidFactoryInterface
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
        return array();
    }
}
