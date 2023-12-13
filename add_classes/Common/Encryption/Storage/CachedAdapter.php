<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

use Closure;
use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\SignatureKeyPair;
use ParagonIE\HiddenString\HiddenString;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * @deprecated `[2022-05-03]` `v2.35` Reason: is not used anymore. No substitution provided.
 */
final class CachedAdapter implements KeyPairStorageInterface
{
    /**
     * The storage cache.
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * The key storage.
     *
     * @var KeyPairStorageInterface
     */
    private $storage;

    /**
     * Creates the cached adapter.
     *
     * @param KeyPairStorageInterface $storage
     */
    public function __construct(KeyPairStorageInterface $storage, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey(string $identifier): bool
    {
        return $this->cache->has($identifier) || $this->storage->hasKey($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function hasKeyPair(string $identifier): bool
    {
        return $this->cache->has($identifier) || $this->storage->hasKeyPair($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignatureKeyPair(string $identifier, SignatureKeyPair $keyPair): bool
    {
        return $this->putToStorages(
            $identifier,
            $keyPair,
            function (string $identifier, SignatureKeyPair $keyPair): bool {
                return $this->storage->storeSignatureKeyPair($identifier, $keyPair);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionKeyPair(string $identifier, EncryptionKeyPair $keyPair): bool
    {
        return $this->putToStorages(
            $identifier,
            $keyPair,
            function (string $identifier, EncryptionKeyPair $keyPair): bool {
                return $this->storage->storeEncryptionKeyPair($identifier, $keyPair);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeKeyPair(string $identifier, HiddenString $keyPairContent): bool
    {
        return $this->putToStorages(
            $identifier,
            $keyPairContent,
            function (string $identifier, HiddenString $keyPairContent): bool {
                return $this->storage->storeKeyPair($identifier, $keyPairContent);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignatureSecretKey(string $identifier, SignatureSecretKey $key): bool
    {
        return $this->putToStorages(
            $identifier,
            $key,
            function (string $identifier, SignatureSecretKey $key): bool {
                return $this->storage->storeSignatureSecretKey($identifier, $key);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignaturePublicKey(string $identifier, SignaturePublicKey $key): bool
    {
        return $this->putToStorages(
            $identifier,
            $key,
            function (string $identifier, SignaturePublicKey $key): bool {
                return $this->storage->storeSignaturePublicKey($identifier, $key);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionSecretKey(string $identifier, EncryptionSecretKey $key): bool
    {
        return $this->putToStorages(
            $identifier,
            $key,
            function (string $identifier, EncryptionSecretKey $key): bool {
                return $this->storage->storeEncryptionSecretKey($identifier, $key);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionPublicKey(string $identifier, EncryptionPublicKey $key): bool
    {
        return $this->putToStorages(
            $identifier,
            $key,
            function (string $identifier, EncryptionPublicKey $key): bool {
                return $this->storage->storeEncryptionPublicKey($identifier, $key);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function storeKey(string $identifier, HiddenString $keyContent): bool
    {
        return $this->putToStorages(
            $identifier,
            $keyContent,
            function (string $identifier, HiddenString $keyContent): bool {
                return $this->storage->storeKey($identifier, $keyContent);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readSignatureKeyPair(string $identifier): SignatureKeyPair
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): SignatureKeyPair {
                return $this->storage->readSignatureKeyPair($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionKeyPair(string $identifier): EncryptionKeyPair
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): EncryptionKeyPair {
                return $this->storage->readEncryptionKeyPair($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readKeyPair(string $identifier): HiddenString
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): HiddenString {
                return $this->storage->readKeyPair($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readSignatureSecretKey(string $identifier): SignatureSecretKey
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): SignatureSecretKey {
                return $this->storage->readSignatureSecretKey($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readSignaturePublicKey(string $identifier): SignaturePublicKey
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): SignaturePublicKey {
                return $this->storage->readSignaturePublicKey($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionSecretKey(string $identifier): EncryptionSecretKey
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): EncryptionSecretKey {
                return $this->storage->readEncryptionSecretKey($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionPublicKey(string $identifier): EncryptionPublicKey
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): EncryptionPublicKey {
                return $this->storage->readEncryptionPublicKey($identifier);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function readKey(string $identifier): HiddenString
    {
        return $this->readCachedContent(
            $identifier,
            function (string $identifier): HiddenString {
                return $this->storage->readKey($identifier);
            }
        );
    }

    /**
     * Reads the object from storages if exists.
     *
     * @param string $identifier
     * @param string $fallbackMethodName
     *
     * @throws StorageException if key/keypair was not found
     *
     * @return object
     */
    private function readCachedContent(string $identifier, Closure $reader): object
    {
        // Cache hit
        if ($this->cache->has($identifier)) {
            // No matter what inside - we got a hit.
            return $this->cache->get($identifier);
        }

        try {
            // Reading from storage
            $storedContent = $reader($identifier);
            if (null === $storedContent) {
                throw new RuntimeException('The key/keypair is not found in storage.');
            }
        } catch (\Exception $exception) {
            throw new StorageException('The key/keypair is not found in the storage', 0, $exception);
        }

        // Store in the cache
        $this->cache->set($identifier, $storedContent);

        return $storedContent;
    }

    /**
     * Puts the key/keypair into storages.
     *
     * @param string $identifier
     * @param object $keyObject
     * @param string $fallbackMethodName
     *
     * @return bool
     */
    private function putToStorages(string $identifier, object $keyObject, \Closure $writer): bool
    {
        try {
            // Write to storage
            $writeResult = $writer($identifier, $keyObject);
        } catch (\Exception $exception) {
            throw new StorageException('Failed to write key/keypair to the storage.', 0, $exception);
        }

        if ($writeResult) {
            // Update cache record
            $this->cache->set($identifier, $keyObject);
        }

        return $writeResult;
    }
}
