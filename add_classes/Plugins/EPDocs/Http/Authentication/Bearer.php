<?php

namespace App\Plugins\EPDocs\Http\Authentication;

use App\Plugins\EPDocs\Http\Authentication;
use Psr\Http\Message\RequestInterface;

final class Bearer implements Authentication
{
    /**
     * The token.
     *
     * @var string
     */
    private $token;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    /**
     * Authenticates the request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function authenticate(RequestInterface $request)
    {
        return $request->withHeader('Authorization', "Bearer {$this->token}");
    }

    /**
     * Authenticates with provided token.
     *
     * @param mixed $token
     *
     * @return self
     */
    public function withToken($token)
    {
        return new self($token);
    }
}
