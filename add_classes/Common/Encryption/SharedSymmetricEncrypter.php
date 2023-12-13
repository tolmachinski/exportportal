<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use App\Common\Encryption\Asymmetric\SharedKeyPairInterface;
use ParagonIE\Halite\Halite;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\AuthenticationKey;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

use function random_bytes;

use const SODIUM_CRYPTO_PWHASH_SALTBYTES;

final class SharedSymmetricEncrypter
{
    /**
     * The key's salt.
     *
     * @var HiddenString
     */
    private $salt;

    /**
     * The encryption encoder algorithm.
     *
     * @var string
     */
    private $encoder;

    /**
     * The shared key pair.
     *
     * @var SharedKeyPairInterface
     */
    private $sharedKeyPair;

    /**
     * The 'rx' encryption key.
     *
     * @var EncryptionKey
     */
    private $rxEncryptionKey;

    /**
     * The 'tx' encryption key.
     *
     * @var EncryptionKey
     */
    private $txEncryptionKey;

    /**
     * The 'rx' authentication key.
     *
     * @var AuthenticationKey
     */
    private $rxAuthenticationKey;

    /**
     * The 'tx' authentication key.
     *
     * @var AuthenticationKey
     */
    private $txAuthenticationKey;

    public function __construct(
        SharedKeyPairInterface $keyPair,
        ?HiddenString $salt = null,
        string $encoder = Halite::ENCODE_BASE64URLSAFE
    )
    {
        $this->salt = $salt ?? new HiddenString(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));
        $this->encoder = $encoder;
        $this->sharedKeyPair = $keyPair;
    }

    /**
     * Encrypts the provided message.
     *
     * @param HiddenString $message
     * @param string       $key
     * @param bool         $serialize
     *
     * @return EncryptedString
     */
    public function encrypt(HiddenString $message, bool $serialize = false): EncryptedString
    {
        $encoding = $serialize ? $this->encoder : Halite::ENCODE_HEX;
        $ciphertext = Crypto::encrypt($message, $this->getRxEncryptionKey(), $encoding);
        $mac = Crypto::authenticate($ciphertext, $this->getRxAuthenticationKey(), $encoding);

        return new EncryptedString(
            new HiddenString($ciphertext),
            new HiddenString($mac)
        );
    }

    /**
     * Decrypts the provided message.
     *
     * @param EncryptedString $message
     * @param string          $key
     * @param bool            $serialize
     *
     * @throws DecryptionException if MAC authentication failed
     *
     * @return HiddenString
     */
    public function decrypt(EncryptedString $message, bool $serialize = false): HiddenString
    {
        $encoding = $serialize ? $this->encoder : Halite::ENCODE_HEX;
        $encryptionKey = $this->getTxEncryptionKey();
        $authenticationKey = $this->getTxAuthenticationKey();
        $ciphertext = $message->getCiphertext();
        $mac = $message->getMac() ?? null;
        if (
            null !== $mac
            && !Crypto::verify(
                $ciphertext,
                $authenticationKey,
                $mac,
                $encoding
            )
        ) {
            throw new DecryptionException('The provided message failed MAC authentication.');
        }

        return new HiddenString(Crypto::decrypt($ciphertext, $encryptionKey, $encoding)->getString());
    }

    /**
     * Returns the 'rx' authentication key.
     *
     * @param string $keyMaterial
     *
     * @return EncryptionKey
     */
    public function getRxEncryptionKey(): EncryptionKey
    {
        return $this->rxEncryptionKey ?? (
            $this->rxEncryptionKey = KeyFactory::deriveEncryptionKey(
                new HiddenString($this->sharedKeyPair->getRxKey()->getRawKeyMaterial()),
                $this->salt->getString()
            )
        );
    }

    /**
     * Returns the 'tx' authentication key.
     *
     * @param string $keyMaterial
     *
     * @return EncryptionKey
     */
    public function getTxEncryptionKey(): EncryptionKey
    {
        return $this->txEncryptionKey ?? (
            $this->txEncryptionKey = KeyFactory::deriveEncryptionKey(
                new HiddenString($this->sharedKeyPair->getTxKey()->getRawKeyMaterial()),
                $this->salt->getString()
            )
        );
    }

    /**
     * Returns the 'rx' authentication key.
     *
     * @param string $keyMaterial
     *
     * @return AuthenticationKey
     */
    public function getRxAuthenticationKey(): AuthenticationKey
    {
        return $this->rxAuthenticationKey ?? (
            $this->rxAuthenticationKey = KeyFactory::deriveAuthenticationKey(
                new HiddenString($this->sharedKeyPair->getRxKey()->getRawKeyMaterial()),
                $this->salt->getString()
            )
        );
    }

    /**
     * Returns the 'tx' authentication key.
     *
     * @param string $keyMaterial
     *
     * @return AuthenticationKey
     */
    public function getTxAuthenticationKey(): AuthenticationKey
    {
        return $this->txAuthenticationKey ?? (
            $this->txAuthenticationKey = KeyFactory::deriveAuthenticationKey(
                new HiddenString($this->sharedKeyPair->getTxKey()->getRawKeyMaterial()),
                $this->salt->getString()
            )
        );
    }
}
