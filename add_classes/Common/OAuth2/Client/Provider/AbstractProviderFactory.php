<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider;

abstract class AbstractProviderFactory implements ProviderFactoryInterface
{
    /**
     * Determines if given oAuth2 provider supported by factory.
     */
    public function supports(string $name): bool
    {
        return \in_array($name, $this->getSupportedTypes());
    }

    /**
     * Gets the list of supported types.
     */
    abstract protected function getSupportedTypes(): array;
}
