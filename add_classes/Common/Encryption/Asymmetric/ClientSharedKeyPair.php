<?php

declare(strict_types=1);

namespace App\Common\Encryption\Asymmetric;

use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\HiddenString\HiddenString;

final class ClientSharedKeyPair implements SharedKeyPairInterface
{
    /**
     * The 'rx' key.
     *
     * @var KeyInterface
     */
    private $rxKey;

    /**
     * The 'tx' key.
     *
     * @var KeyInterface
     */
    private $txKey;

    public function __construct(EncryptionKeyPair $clientKeyPair, EncryptionPublicKey $serverPublicKey)
    {
        list($rxKey, $txKey) = \sodium_crypto_kx_client_session_keys(
            \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                $clientKeyPair->getSecretKey()->getRawKeyMaterial(),
                $clientKeyPair->getPublicKey()->getRawKeyMaterial()
            ),
            $serverPublicKey->getRawKeyMaterial()
        );

        $this->rxKey = new SharedKey(new HiddenString($rxKey));
        $this->txKey = new SharedKey(new HiddenString($txKey));
    }

    /**
     * Returns the 'rx' key of the shared key pair.
     *
     * @return KeyInterface
     */
    public function getRxKey(): KeyInterface
    {
        return $this->rxKey;
    }

    /**
     * Returns the 'tx' key of the shared key pair.
     *
     * @return KeyInterface
     */
    public function getTxKey(): KeyInterface
    {
        return $this->txKey;
    }
}
