<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use ParagonIE\Halite\Util;
use ParagonIE\HiddenString\HiddenString;

final class EncryptedString
{
    /**
     * The mac string.
     *
     * @var string
     */
    private $mac;

    /**
     * The ciphertext.
     *
     * @var string
     */
    private $ciphertext;

    public function __construct(HiddenString $ciphertext, HiddenString $mac = null)
    {
        $this->mac = null !== $mac ? Util::safeStrcpy($mac->getString()) : null;
        $this->ciphertext = Util::safeStrcpy($ciphertext->getString());
    }

    /**
     * Make sure you wipe the key from memory on destruction.
     */
    public function __destruct()
    {
        \sodium_memzero($this->ciphertext);
        if (null !== $this->mac) {
            \sodium_memzero($this->mac);
        }
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
        return array(
            'mac'        => '*',
            'ciphertext' => '*',
            'attention'  => 'If you need the value of a EncryptedString, use methods getCiphertext() or getMac() instead of dumping it.',
        );
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
        return Util::safeStrcpy("{$this->mac}{$this->ciphertext}");
    }

    /**
     * Returns the MAC of the string.
     *
     * @return string
     */
    public function getMac(): string
    {
        return Util::safeStrcpy($this->mac);
    }

    /**
     * Returns the ciphertext.
     *
     * @return string
     */
    public function getCiphertext(): string
    {
        return Util::safeStrcpy($this->ciphertext);
    }
}
