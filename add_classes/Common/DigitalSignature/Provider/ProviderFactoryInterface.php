<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

interface ProviderFactoryInterface
{
    /**
     * Creates instance of the signing service.
     */
    public function create(array $options = [], array $collaborators = []): ProviderInterface;

    /**
     * Determines supoort of the factory by provided name.
     */
    public function supports(string $type): bool;
}
