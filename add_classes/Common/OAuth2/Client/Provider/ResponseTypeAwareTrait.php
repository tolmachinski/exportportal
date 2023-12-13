<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider;

trait ResponseTypeAwareTrait
{
    /**
     * The default value of the response type that tells the authorization server which grant to execute.
     */
    protected string $defaultResponseType = 'code';

    /**
     * Get the default value of the response type that tells the authorization server which grant to execute.
     */
    public function getDefaultResponseType(): string
    {
        return $this->defaultResponseType;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationParameters(array $options)
    {
        return parent::getAuthorizationParameters(
            $options += [
                'response_type' => $this->defaultResponseType,
            ]
        );
    }
}
