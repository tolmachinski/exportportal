<?php

declare(strict_types=1);

namespace App\Common\Encryption;

use App\Common\Encryption\Storage\FileKeyStorage;
use App\Common\Encryption\Storage\KeyFilePathGenerator;
use ParagonIE\Halite\SignatureKeyPair;

trait MasterKeyAwareTrait
{
    /**
     * Gets the master key.
     */
    private function getMasterKey(): SignatureKeyPair
    {
        /** @var FileKeyStorage */
        $keyStorage = $this->getContainer()->get(FileKeyStorage::class);

        return $keyStorage->readSignatureKeyPair(
            (new KeyFilePathGenerator())->createIdentifier(config('env.APP_ENCRYPTION_KEY_NAME'))
        )
        ;
    }
}
