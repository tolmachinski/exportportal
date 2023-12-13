<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs;

class Configuration
{
    /**
     * The HTTP origin.
     */
    private ?string $httpOrigin;

    /**
     * The default user ID.
     */
    private ?string $defaultUserId;

    /**
     * Get the HTTP origin.
     */
    public function getHttpOrigin(): ?string
    {
        return $this->httpOrigin;
    }

    /**
     * Set the HTTP origin.
     *
     * @return $this
     */
    public function setHttpOrigin(?string $httpOrigin): self
    {
        $this->httpOrigin = $httpOrigin;

        return $this;
    }

    /**
     * Get the default user ID.
     */
    public function getDefaultUserId(): ?string
    {
        return $this->defaultUserId;
    }

    /**
     * Set the default user ID.
     *
     * @return $this
     */
    public function setDefaultUserId(?string $defaultUserId): self
    {
        $this->defaultUserId = $defaultUserId;

        return $this;
    }
}
