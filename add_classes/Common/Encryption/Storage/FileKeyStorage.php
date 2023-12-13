<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\SignatureKeyPair;
use ParagonIE\HiddenString\HiddenString;

final class FileKeyStorage implements KeyPairStorageInterface
{
    /**
     * The file storage.
     */
    private FilesystemOperator $fileStorage;

    /**
     * The directory where the key is located.
     */
    private string $directory;

    /**
     * Creates the instance of the key file storage.
     */
    public function __construct(FilesystemOperator $fileStorage, string $directory)
    {
        $this->directory = \rtrim($directory, '\\/');
        $this->fileStorage = $fileStorage;
    }

    /**
     * Returns the new instance with another directory.
     *
     * @return static
     */
    public function withDirectory(string $directory): self
    {
        $instance = clone $this;
        $instance->directory = \rtrim($directory, '\\/');

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey(string $identifier): bool
    {
        return $this->fileStorage->fileExists("{$this->directory}/{$identifier}");
    }

    /**
     * {@inheritdoc}
     */
    public function hasKeyPair(string $identifier): bool
    {
        return $this->fileStorage->fileExists("{$this->directory}/{$identifier}");
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignatureKeyPair(string $identifier, SignatureKeyPair $keyPair): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($keyPair));
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionKeyPair(string $identifier, EncryptionKeyPair $keyPair): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($keyPair));
    }

    /**
     * {@inheritdoc}
     */
    public function storeKeyPair(string $identifier, HiddenString $keyPairContent): bool
    {
        return $this->putToStorage($identifier, $keyPairContent);
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignatureSecretKey(string $identifier, SignatureSecretKey $key): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($key));
    }

    /**
     * {@inheritdoc}
     */
    public function storeSignaturePublicKey(string $identifier, SignaturePublicKey $key): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($key));
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionSecretKey(string $identifier, EncryptionSecretKey $key): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($key));
    }

    /**
     * {@inheritdoc}
     */
    public function storeEncryptionPublicKey(string $identifier, EncryptionPublicKey $key): bool
    {
        return $this->putToStorage($identifier, KeyFactory::export($key));
    }

    /**
     * {@inheritdoc}
     */
    public function storeKey(string $identifier, HiddenString $keyContent): bool
    {
        return $this->putToStorage($identifier, $keyContent);
    }

    /**
     * {@inheritdoc}
     */
    public function readSignatureKeyPair(string $identifier): SignatureKeyPair
    {
        return KeyFactory::importSignatureKeyPair($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionKeyPair(string $identifier): EncryptionKeyPair
    {
        return KeyFactory::importEncryptionKeyPair($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readKeyPair(string $identifier): HiddenString
    {
        return $this->readFromStorages($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function readSignatureSecretKey(string $identifier): SignatureSecretKey
    {
        return KeyFactory::importSignatureSecretKey($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readSignaturePublicKey(string $identifier): SignaturePublicKey
    {
        return KeyFactory::importSignaturePublicKey($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionSecretKey(string $identifier): EncryptionSecretKey
    {
        return KeyFactory::importEncryptionSecretKey($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readEncryptionPublicKey(string $identifier): EncryptionPublicKey
    {
        return KeyFactory::importEncryptionPublicKey($this->readFromStorages($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function readKey(string $identifier): HiddenString
    {
        return $this->readFromStorages($identifier);
    }

    /**
     * Reads the key and keypair from storage.
     *
     * @throws ReadException if file not found
     */
    public function readFromStorages(string $identifier): HiddenString
    {
        try {
            $content = $this->fileStorage->read("{$this->directory}/{$identifier}");
            if (null === $content || false === $content) {
                throw new \RuntimeException('Result cannot be null or false');
            }
        } catch (\RuntimeException | UnableToReadFile $exception) {
            throw new ReadException('Failed to read the file.', 0, $exception);
        }

        return new HiddenString((string) $content);
    }

    /**
     * Stores the content to storage.
     */
    private function putToStorage(string $identifier, HiddenString $content): bool
    {
        try {
            $this->fileStorage->write("{$this->directory}/{$identifier}", $content->getString());

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
