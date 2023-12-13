<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Provider;

interface ResponseTypeAwareProviderInterface
{
    /**
     * Get the default value of the response type that tells the authorization server which grant to execute.
     */
    public function getDefaultResponseType(): string;
}
