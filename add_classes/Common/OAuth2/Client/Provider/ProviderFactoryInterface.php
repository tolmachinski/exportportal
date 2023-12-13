<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;

interface ProviderFactoryInterface
{
    /**
     * Creates the code grant service for given oAuth2 provider.
     */
    public function create(array $options): AbstractProvider;

    /**
     * Determines if given oAuth2 provider supported by factory.
     */
    public function supports(string $name): bool;
}
