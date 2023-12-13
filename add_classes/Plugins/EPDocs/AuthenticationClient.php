<?php

namespace App\Plugins\EPDocs;

use Psr\Http\Message\RequestInterface;

interface AuthenticationClient
{
    /**
     * Performs authentication of the request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function doAuthenticate(RequestInterface $requestInterface);
}
