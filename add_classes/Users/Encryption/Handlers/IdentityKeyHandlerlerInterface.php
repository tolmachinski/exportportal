<?php

declare(strict_types=1);

namespace App\Users\Encryption\Handlers;

use ParagonIE\Halite\SignatureKeyPair;

interface IdentityKeyHandlerlerInterface
{
    /**
     * Returns the identity key pair.
     *
     * @param string $userSignature
     *
     * @return SignatureKeyPair
     */
    public function getIdenityKeyPair(string $userSignature): SignatureKeyPair;
}
