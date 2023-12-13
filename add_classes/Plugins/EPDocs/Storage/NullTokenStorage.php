<?php

namespace App\Plugins\EPDocs\Storage;

use App\Plugins\EPDocs\TokenStorage;

final class NullTokenStorage implements TokenStorage
{
    /**
     * Returns stored credentials.
     */
    public function getToken()
    {
        return null;
    }

    /**
     * Renews the token.
     *
     * @return self
     */
    public function renewToken()
    {
        // Here be dragons
    }
}
