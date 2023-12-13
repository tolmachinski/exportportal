<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

trait ProviderResolverAwareTrait
{
    /**
     * The signing providers resolver instance.
     */
    private ?ProviderResolverInterface $providerResolver;

    /**
     * Sets the signing providers resolver instance.
     */
    public function setProviderResolver(?ProviderResolverInterface $providerResolver): void
    {
        $this->providerResolver = $providerResolver;
    }

    /**
     * Gets the signing providers resolver instance.
     */
    public function getProviderResolver(): ?ProviderResolverInterface
    {
        return $this->providerResolver;
    }
}
