<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Token\Storage;

use League\OAuth2\Client\Token\AccessToken;

interface StorageInterface
{
    /**
     * Get the storage options.
     */
    public function getOptions(): array;

    /**
     * Sets the options for storage.
     */
    public function updateOptions(array $options = []): void;

    /**
     * Reads from the access token from storage.
     */
    public function readAccessToken(): ?AccessToken;

    /**
     * Writes access token into the storage.
     */
    public function writeAccessToken(AccessToken $accessToken): void;

    /**
     * Removes access token from storage.
     */
    public function removeAccessToken(): void;
}
