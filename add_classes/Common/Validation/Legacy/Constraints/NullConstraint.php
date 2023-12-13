<?php

namespace App\Common\Validation\Legacy\Constraints;

use App\Common\Validation\Legacy\ConstraintInterface;
use App\Common\Validation\ValidationDataInterface;
use DomainException;

final class NullConstraint implements ConstraintInterface
{
    /**
     * The legacy metadata.
     *
     * @var array
     */
    private $legacyMetadata;

    public function __construct(array $metadata = array())
    {
        $this->legacyMetadata = $metadata;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DomainException on call
     */
    public function assert(ValidationDataInterface $data)
    {
        throw new DomainException('The assert() method is not illegible for legacy constraint.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(): array
    {
        return $this->legacyMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadata(array $metadata = array()): void
    {
        $this->legacyMetadata = $metadata;
    }
}
