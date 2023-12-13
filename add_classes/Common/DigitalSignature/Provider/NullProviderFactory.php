<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

class NullProviderFactory implements ProviderFactoryInterface
{
    /**
     * Creates instance of the signing service.
     */
    public function create(array $options = [], array $collaborators = []): ProviderInterface
    {
        return new NullProvider();
    }

    /**
     * Determines supoort of the factory by provided name.
     */
    public function supports(string $type): bool
    {
        return NullProvider::class === $type;
    }
}
