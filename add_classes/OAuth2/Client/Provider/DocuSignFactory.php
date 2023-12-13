<?php

declare(strict_types=1);

namespace App\OAuth2\Client\Provider;

use App\Common\OAuth2\Client\Provider\AbstractProviderFactory;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;

class DocuSignFactory extends AbstractProviderFactory
{
    /**
     * Creates the code grant service for given oAuth2 provider.
     */
    public function create(array $options): AbstractProvider
    {
        return (new DocuSign($options))->setOptionProvider(new HttpBasicAuthOptionProvider());
    }

    /**
     * Gets the list of supported types.
     */
    protected function getSupportedTypes(): array
    {
        return [
            DocuSign::class,
        ];
    }
}
