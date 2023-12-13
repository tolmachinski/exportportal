<?php

declare(strict_types=1);

namespace App\Common\Encryption\Handlers;

use App\Common\Encryption\Storage\KeyPairStorageInterface;
use DomainException;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\SignatureKeyPair;
use RuntimeException;

abstract class AbstractKeyHandler
{
    /**
     * The key storage.
     *
     * @var KeyPairStorageInterface
     */
    protected $keyStorage;

    public function __construct(KeyPairStorageInterface $keyStorage)
    {
        $this->keyStorage = $keyStorage;
    }

    /**
     * Returns or creates the signature key.
     *
     * @param string   $keyPath
     * @param callable $factory
     *
     * @throws DomainException  if factory creates invalid key
     * @throws RuntimeException if failed to store the key
     *
     * @return SignatureKeyPair
     */
    protected function getOrCreateSignatureKeyPair(string $keyPath, callable $factory): SignatureKeyPair
    {
        if (!$this->keyStorage->hasKeyPair($keyPath)) {
            $keyPair = $factory();
            if (!$keyPair instanceof SignatureKeyPair && !$keyPair instanceof SignatureSecretKey) {
                throw new DomainException(sprintf(
                    'The key factory must create an instance of %s or %s',
                    SignatureSecretKey::class,
                    SignatureKeyPair::class
                ));
            }
            if ($keyPair instanceof SignatureSecretKey) {
                $keyPair = new SignatureKeyPair($keyPair);
            }

            if (!$this->keyStorage->storeSignatureKeyPair($keyPath, $keyPair)) {
                throw new RuntimeException('Failed to create new signature key pair.');
            }

            return $keyPair;
        }

        return $this->keyStorage->readSignatureKeyPair($keyPath);
    }

    /**
     * Returns or creates the encryption key.
     *
     * @param string   $keyPath
     * @param callable $factory
     *
     * @throws DomainException  if factory creates invalid key
     * @throws RuntimeException if failed to store the key
     *
     * @return EncryptionKeyPair
     */
    protected function getOrCreateEncryptionKeyPair(string $keyPath, callable $factory): EncryptionKeyPair
    {
        if (!$this->keyStorage->hasKeyPair($keyPath)) {
            $keyPair = $factory();
            if (!$keyPair instanceof EncryptionKeyPair && !$keyPair instanceof EncryptionSecretKey) {
                throw new DomainException(sprintf(
                    'The key factory must create an instance of %s or %s',
                    EncryptionSecretKey::class,
                    EncryptionKeyPair::class
                ));
            }
            if ($keyPair instanceof EncryptionSecretKey) {
                $keyPair = new EncryptionKeyPair($keyPair);
            }

            if (!$this->keyStorage->storeEncryptionKeyPair($keyPath, $keyPair)) {
                throw new RuntimeException('Failed to create new encryption key pair.');
            }

            return $keyPair;
        }

        return $this->keyStorage->readEncryptionKeyPair($keyPath);
    }
}
