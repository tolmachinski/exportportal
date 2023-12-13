<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\SignatureKeyPair;
use ParagonIE\HiddenString\HiddenString;

interface KeyPairStorageInterface extends KeyStorageInterface
{
    /**
     * Checks if key pair with provided identifier exists in storage.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasKeyPair(string $identifier): bool;

    /**
     * Stores the signature key pair.
     *
     * @param string           $identifier
     * @param SignatureKeyPair $keyPair
     *
     * @return bool
     */
    public function storeSignatureKeyPair(string $identifier, SignatureKeyPair $keyPair): bool;

    /**
     * Stores the encryption key pair.
     *
     * @param string            $identifier
     * @param EncryptionKeyPair $keyPair
     *
     * @return bool
     */
    public function storeEncryptionKeyPair(string $identifier, EncryptionKeyPair $keyPair): bool;

    /**
     * Stores the key pair contents.
     *
     * @param string       $identifier
     * @param HiddenString $keyPairContent
     *
     * @return bool
     */
    public function storeKeyPair(string $identifier, HiddenString $keyPairContent): bool;

    /**
     * Reads the signature key pair using provided identifier.
     *
     * @param string $identifier
     *
     * @return SignatureKeyPair
     */
    public function readSignatureKeyPair(string $identifier): SignatureKeyPair;

    /**
     * Reads the encryption key pair using provided identifier.
     *
     * @param string $identifier
     *
     * @return EncryptionKeyPair
     */
    public function readEncryptionKeyPair(string $identifier): EncryptionKeyPair;

    /**
     * Reads the key pair contents using provided identifier.
     *
     * @param string $identifier
     *
     * @return HiddenString
     */
    public function readKeyPair(string $identifier): HiddenString;
}
