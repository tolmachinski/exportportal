<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

interface ProviderResolverInterface
{
    /**
     * Resolves the service by its name.
     */
    public function resolve(string $type): ?ProviderInterface;
}
