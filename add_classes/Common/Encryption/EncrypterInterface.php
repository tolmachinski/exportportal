<?php

declare(strict_types=1);

namespace App\Common\Encryption;

interface EncrypterInterface
{
    /**
     * Encrypts the provided message.
     *
     * @param string $message
     * @param string $key
     * @param bool   $serialize
     *
     * @return string
     */
    public function encrypt($message, $key, $serialize = false);
}
