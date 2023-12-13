<?php

namespace App\Plugins\EPDocs\Validator;

use App\Plugins\EPDocs\TokenValidationAdapter;

final class NullTokenValidator implements TokenValidationAdapter
{
    /**
     * Validates the token.
     *
     * @param mixed $token
     *
     * @return bool
     */
    public function validateToken($token)
    {
        return true;
    }
}
