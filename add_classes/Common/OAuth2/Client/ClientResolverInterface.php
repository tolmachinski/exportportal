<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client;

use App\Common\Exceptions\NotFoundException;

interface ClientResolverInterface
{
    /**
     * Get the OAuth2 client.
     *
     * @throws NotFoundException if client with this types is not found
     */
    public function client(string $type): ClientInterface;
}
