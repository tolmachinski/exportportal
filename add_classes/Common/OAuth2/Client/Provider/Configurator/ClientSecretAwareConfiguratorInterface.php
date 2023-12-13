<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider\Configurator;

interface ClientSecretAwareConfiguratorInterface
{
    /**
     * Determine if client secret is requruired.
     */
    public function needsClientSecret(): bool;
}
