<?php

declare(strict_types=1);

namespace App\Common\Encryption\Asymmetric;

interface SharedKeyPairInterface
{
    /**
     * Returns the 'rx' key of the shared key pair.
     *
     * @return KeyInterface
     */
    public function getRxKey(): KeyInterface;

    /**
     * Returns the 'tx' key of the shared key pair.
     *
     * @return KeyInterface
     */
    public function getTxKey(): KeyInterface;
}
