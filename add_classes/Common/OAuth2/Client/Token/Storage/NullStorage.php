<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Token\Storage;

use League\OAuth2\Client\Token\AccessToken;

class NullStorage implements StorageInterface
{
    /**
     * Get the storage options.
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * Sets the options for storage.
     */
    public function updateOptions(array $options = []): void
    {
        // Do nothing
    }

    /**
     * Reads from the access token from storage.
     */
    public function readAccessToken(): ?AccessToken
    {
        return null;
    }

    /**
     * Writes access token into the storage.
     */
    public function writeAccessToken(AccessToken $accessToken): void
    {
        // Do nothing
    }

    /**
     * Removes access token from storage.
     */
    public function removeAccessToken(): void
    {
        // Do nothing
    }
}
