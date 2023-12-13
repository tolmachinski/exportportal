<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\HiddenString\HiddenString;

interface KeyStorageInterface
{
    /**
     * Returns the new instance with another directory.
     *
     * @return static
     */
    public function withDirectory(string $directory): self;

    /**
     * Checks if key with provided identifier exists in storage.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasKey(string $identifier): bool;

    /**
     * Stores the signature key.
     *
     * @param string             $identifier
     * @param SignatureSecretKey $key
     *
     * @return bool
     */
    public function storeSignatureSecretKey(string $identifier, SignatureSecretKey $key): bool;

    /**
     * Stores the signature key.
     *
     * @param string             $identifier
     * @param SignaturePublicKey $key
     *
     * @return bool
     */
    public function storeSignaturePublicKey(string $identifier, SignaturePublicKey $key): bool;

    /**
     * Stores the signature key.
     *
     * @param string              $identifier
     * @param EncryptionSecretKey $key
     *
     * @return bool
     */
    public function storeEncryptionSecretKey(string $identifier, EncryptionSecretKey $key): bool;

    /**
     * Stores the signature key.
     *
     * @param string              $identifier
     * @param EncryptionPublicKey $key
     *
     * @return bool
     */
    public function storeEncryptionPublicKey(string $identifier, EncryptionPublicKey $key): bool;

    /**
     * Stores the key content.
     *
     * @param string       $identifier
     * @param HiddenString $keyContent
     *
     * @return bool
     */
    public function storeKey(string $identifier, HiddenString $keyContent): bool;

    /**
     * Reads the signature key using provided identifier.
     *
     * @param string $identifier
     *
     * @return SignatureSecretKey
     */
    public function readSignatureSecretKey(string $identifier): SignatureSecretKey;

    /**
     * Reads the signature key using provided identifier.
     *
     * @param string $identifier
     *
     * @return SignaturePublicKey
     */
    public function readSignaturePublicKey(string $identifier): SignaturePublicKey;

    /**
     * Reads the signature key using provided identifier.
     *
     * @param string $identifier
     *
     * @return EncryptionSecretKey
     */
    public function readEncryptionSecretKey(string $identifier): EncryptionSecretKey;

    /**
     * Reads the signature key using provided identifier.
     *
     * @param string $identifier
     *
     * @return EncryptionPublicKey
     */
    public function readEncryptionPublicKey(string $identifier): EncryptionPublicKey;

    /**
     * Reads the key content using provided identifier.
     *
     * @param string $identifier
     *
     * @return HiddenString
     */
    public function readKey(string $identifier): HiddenString;
}
