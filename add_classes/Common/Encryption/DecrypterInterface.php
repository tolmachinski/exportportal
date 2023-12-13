<?php

declare(strict_types=1);

namespace App\Common\Encryption;

interface DecrypterInterface
{
    /**
     * Decrypts the provided message.
     *
     * @param string $message
     * @param string $key
     * @param bool   $serialize
     *
     * @return string
     */
    public function decrypt($message, $key, $serialize = false);
}
