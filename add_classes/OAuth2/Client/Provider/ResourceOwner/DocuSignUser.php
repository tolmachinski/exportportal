<?php

declare(strict_types=1);

namespace App\OAuth2\Client\Provider\ResourceOwner;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class DocuSignUser implements ResourceOwnerInterface
{
    protected ?string $targetAccountId;

    /**
     * Raw response.
     */
    protected array $response;

    /**
     * The default or selected account.
     * If targetAccountId option was set then that account will be selected.
     * Else (usual case), the user's default account will be selected.
     *
     * @psalm-param array{account_id: string, is_default: bool, account_name: string, base_url: stirng, organization: ?array}
     *
     * Example:
     *      "account_id": "7f09961a-a22e-4ea2-8395-aaaaaaaaaaaa",
     *      "is_default": true,
     *      "account_name": "ACME Supplies",
     *      "base_uri": "https://demo.docusign.net",
     *      "organization": {
     *          "organization_id": "9dd9d6cd-7ad1-461a-a432-aaaaaaaaaaaa",
     *          "links": [
     *              {
     *                  "rel": "self",
     *                  "href": "https://account-d.docusign.com/organizations/9dd9d6cd-7ad1-461a-a432-aaaaaaaaaaaa"
     *              }
     *          ]
     *      }
     */
    protected ?array $accountInfo = null;

    /**
     * Creates new resource owner.
     *
     * @throws \Exception if an account is selected but not found
     */
    public function __construct(array $response = [], ?string $targetAccountId = null)
    {
        $this->response = $response;
        $this->targetAccountId = $targetAccountId;

        // Find the selected or default account
        if (null !== $this->targetAccountId) {
            foreach ($response['accounts'] as $accountInfo) {
                if ($accountInfo['account_id'] === $this->target_account_id) {
                    $this->accountInfo = $accountInfo;

                    break;
                }
            }

            if (null === $this->accountInfo) {
                throw new \Exception('Targeted Account Id is not found.');
            }
        } else {
            // Find the default account info
            foreach ($response['accounts'] as $accountInfo) {
                if ($accountInfo['is_default']) {
                    $this->accountInfo = $accountInfo;

                    break;
                }
            }

            if (null === $this->accountInfo) {
                throw new \Exception('Account is not found.');
            }
        }
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * Get resource owner id.
     */
    public function getUserId(): ?string
    {
        return $this->response['sub'] ?: null;
    }

    /**
     * Get resource owner email.
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Get resource owner name.
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get selected account info.
     */
    public function getAccountInfo(): ?array
    {
        return $this->accountInfo;
    }

    /**
     * Get array of account info for the user's accounts
     * An account's info may include organization info.
     *
     * @return array
     */
    public function getAccounts()
    {
        return $this->response['accounts'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
