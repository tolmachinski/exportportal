<?php

declare(strict_types=1);

namespace App\DigitalSignature\Provider\DocuSign;

use App\Common\OAuth2\Client\ClientInterface as AuthClientInterface;
use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\BulkEnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\FoldersApi;
use DocuSign\eSign\Api\GroupsApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Client\ApiClient;

trait ApiAwareTrait
{
    /**
     * DocuSign API Client.
     */
    private ApiClient $apiClient;

    /**
     * The authentication client.
     */
    private AuthClientInterface $authClient;

    /**
     * Get docuSign API Client.
     */
    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }

    /**
     * Get the authentication client.
     */
    public function getAuthClient(): AuthClientInterface
    {
        return $this->authClient;
    }

    /**
     * Getter for the EnvelopesApi.
     */
    private function getEnvelopeApi(): EnvelopesApi
    {
        return new EnvelopesApi($this->getApiClient());
    }

    /**
     * Getter for the FoldersApi.
     */
    private function getFoldersApi(): FoldersApi
    {
        return new FoldersApi($this->getApiClient());
    }

    /**
     * Getter for the TemplatesApi.
     */
    private function getTemplatesApi(): TemplatesApi
    {
        return new TemplatesApi($this->getApiClient());
    }

    /**
     * Getter for the AccountsApi.
     */
    private function getAccountsApi(): AccountsApi
    {
        return new AccountsApi($this->getApiClient());
    }

    /**
     * Getter for the AccountsApi.
     */
    private function getGroupsApi(): GroupsApi
    {
        return new GroupsApi($this->getApiClient());
    }

    /**
     * Getter for the BulkEnvelopesApi.
     */
    private function getBulkEnvelopesApi(): BulkEnvelopesApi
    {
        return new BulkEnvelopesApi($this->getApiClient());
    }
}
