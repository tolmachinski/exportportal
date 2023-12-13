<?php

namespace App\Plugins\EPDocs;

interface TokenValidationAdapter
{
    /**
     * Validates the token.
     *
     * @param mixed $token
     *
     * @return bool
     */
    public function validateToken($token);
}
