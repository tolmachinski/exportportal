<?php

namespace App\Plugins\EPDocs;

interface TokenStorage
{
    /**
     * Returns stored toekn.
     *
     * @return mixed
     */
    public function getToken();

    /**
     * Renews the token.
     */
    public function renewToken();
}
