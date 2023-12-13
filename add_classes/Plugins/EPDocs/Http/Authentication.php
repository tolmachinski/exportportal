<?php

namespace App\Plugins\EPDocs\Http;

use Psr\Http\Message\RequestInterface;

interface Authentication
{
    /**
     * Authenticates the request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function authenticate(RequestInterface $request);

    /**
     * Authenticates with provided token.
     *
     * @param mixed $token
     *
     * @return self
     */
    public function withToken($token);
}
