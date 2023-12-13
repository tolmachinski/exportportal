<?php

declare(strict_types=1);

namespace App\Common\Encryption\Asymmetric;

use App\Common\Encryption\CloneException;
use App\Common\Encryption\SerializeException;
use ParagonIE\Halite\Util;
use ParagonIE\HiddenString\HiddenString;

final class SharedKey implements KeyInterface
{
    /**
     * The key material.
     *
     * @var string
     */
    private $keyMaterial;

    public function __construct(HiddenString $keyMaterial)
    {
        $this->keyMaterial = Util::safeStrcpy($keyMaterial->getString());
    }

    /**
     * Make sure you wipe the key from memory on destruction.
     */
    public function __destruct()
    {
        \sodium_memzero($this->keyMaterial);
    }

    /**
     * Prevent cloning of this object.
     *
     * @throws CloneException
     */
    public function __clone()
    {
        throw new CloneException();
    }

    /**
     * Hide this from var_dump(), etc.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        // exclude everything
        return array();
    }

    /**
     * Prevent object serialization.
     *
     * @throws SerializeException
     */
    public function __sleep(): void
    {
        throw new SerializeException();
    }

    /**
     * Prevent object deserialization.
     *
     * @throws SerializeException
     */
    public function __wakeup(): void
    {
        throw new SerializeException();
    }

    /**
     * Get the encrypted string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * Returns the raw key material.
     *
     * @return string
     */
    public function getRawKeyMaterial(): string
    {
        return Util::safeStrcpy($this->keyMaterial);
    }
}
