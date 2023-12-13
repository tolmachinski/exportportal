<?php

namespace App\Plugins\EPDocs\Validator;

use App\Plugins\EPDocs\TokenValidationAdapter;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

final class JwtTokenValidator implements TokenValidationAdapter
{
    /**
     * Validates the token.
     *
     * @param \Lcobucci\JWT\Token $token
     *
     * @return bool
     */
    public function validateToken($token)
    {
        if (!$token instanceof Token) {
            return false;
        }

        return $token->validate(new ValidationData());
    }
}
