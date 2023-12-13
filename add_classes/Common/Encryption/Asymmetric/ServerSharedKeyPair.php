<?php

declare(strict_types=1);

namespace App\Common\Encryption\Asymmetric;

use ParagonIE\Halite\Asymmetric\EncryptionPublicKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\HiddenString\HiddenString;

final class ServerSharedKeyPair implements SharedKeyPairInterface
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

    public function __construct(EncryptionKeyPair $serverKeyPair, EncryptionPublicKey $clientPublicKey)
    {
        list($rxKey, $txKey) = \sodium_crypto_kx_server_session_keys(
            \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                $serverKeyPair->getSecretKey()->getRawKeyMaterial(),
                $serverKeyPair->getPublicKey()->getRawKeyMaterial()
            ),
            $clientPublicKey->getRawKeyMaterial()
        );

        $this->rxKey = new SharedKey(new HiddenString($rxKey));
        $this->txKey = new SharedKey(new HiddenString($txKey));
    }

    /**
     * Returns the 'rx' key of the shared key pair.
     *
     * @return string
     */
    public function getRxKey(): KeyInterface
    {
        return $this->rxKey;
    }

    /**
     * Returns the 'tx' key of the shared key pair.
     *
     * @return string
     */
    public function getTxKey(): KeyInterface
    {
        return $this->txKey;
    }
}
