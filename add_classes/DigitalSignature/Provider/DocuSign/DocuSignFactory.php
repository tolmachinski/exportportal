<?php

declare(strict_types=1);

namespace App\DigitalSignature\Provider\DocuSign;

use App\Common\DigitalSignature\Provider\ProviderFactoryInterface;
use App\Common\DigitalSignature\Provider\ProviderInterface;
use App\Common\OAuth2\Client\ClientResolverInterface;
use App\DigitalSignature\Provider\DocuSign\Configuration as ServiceConfiguration;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use InvalidArgumentException;
use Symfony\Component\Config\Definition\Processor;

final class DocuSignFactory implements ProviderFactoryInterface
{
    /**
     * Creates instance of the signing service.
     */
    public function create(array $options = [], array $collaborators = []): ProviderInterface
    {
        $options = $this->processServiceOptions($options);
        $config = new Configuration();
        $config->setHost($options['base_path']);
        $authClientName = $options['auth_client'] ?? null;
        if (null === $authClientName) {
            throw new InvalidArgumentException('The option "auth_client" is required.');
        }

        $authToken = $options['auth_token'] ?? null;
        if (null !== $authToken) {
            $config->addDefaultHeader('Authorization', "Bearer {$authToken}");
        }

        return new DocuSign(
            new ApiClient($config),
            $this->getAuthClientResolver($collaborators)->client($authClientName),
            $options['account_id'] ?? null,
            $options['templates']['email_subject'] ?? null,
            $options['templates']['email_message'] ?? null
        );
    }

    /**
     * Determines supoort of the factory by provided name.
     */
    public function supports(string $type): bool
    {
        return DocuSign::class === $type;
    }

    /**
     * Processes service options.
     */
    private function processServiceOptions(array $options = []): array
    {
        return (new Processor())->processConfiguration(
            new ServiceConfiguration(),
            ['options' => $options]
        );
    }

    /**
     * Gets the OAuth2 clients resolver from collaborators list.
     */
    private function getAuthClientResolver(array $collaborators): ClientResolverInterface
    {
        if (null === $resolver = $collaborators['oauth2_clients'] ?? null) {
            throw new InvalidArgumentException('The collaborator "oauth2_clients" is required');
        }
        if (!$resolver instanceof ClientResolverInterface) {
            throw new InvalidArgumentException(\sprintf('The collaborator "oauth2_clients" must be instance of "%s"', ClientResolverInterface::class));
        }

        return $resolver;
    }
}
