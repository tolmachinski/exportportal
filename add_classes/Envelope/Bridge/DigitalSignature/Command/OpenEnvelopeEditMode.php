<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Command;

use App\Common\Database\Model;
use App\Common\DigitalSignature\Provider\ProviderResolverAwareTrait;
use App\Common\DigitalSignature\Provider\ProviderResolverInterface as SigningProviderResolverInterface;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\DigitalSignature\Message\OpenEnvelopeEditModeMessage;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Event\CreateDigitalDraft;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\CreateDigitalDraftMessage;
use App\Envelope\SigningMecahisms;

final class OpenEnvelopeEditMode
{
    use ProviderResolverAwareTrait {
        ProviderResolverAwareTrait::setProviderResolver as setSigningProviderResolver;
        ProviderResolverAwareTrait::getProviderResolver as getSigningProviderResolver;
    }

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The file storage.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, FileStorageInterface $fileStorage, SigningProviderResolverInterface $signingProviderResolver)
    {
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->setSigningProviderResolver($signingProviderResolver);
    }

    /**
     * Execute the command.
     */
    public function __invoke(OpenEnvelopeEditModeMessage $message)
    {
        $envelope = $this->getEnvelopeFromStorage($envelopeId = $message->getEnvelopeId());
        if (EnvelopeStatuses::NOT_PROCESSED !== $envelope['status']) {
            throw new AccessDeniedException(
                \sprintf('Only envelopes in the status "%s" are accepted', EnvelopeStatuses::NOT_PROCESSED)
            );
        }

        // Add values to final redirect URL if exists
        if (null !== $redirectUrl = $message->getRedirectUrl()) {
            $separator = null === parse_url($redirectUrl, PHP_URL_QUERY) ? '?' : '&';
            $redirectUrl = \sprintf('%s%s%s', $redirectUrl, $separator, \http_build_query(\arrayCamelizeAssocKeys([
                'original_envelope_id' => (string) $envelope['uuid'],
            ])));
        }

        // Get the remote envelope ID (if exists)
        $remoteEnvelopeId = (new CreateDigitalDraft($this->envelopesRepository, $this->fileStorage, $this->getSigningProviderResolver()))->__invoke(
            new CreateDigitalDraftMessage($envelopeId)
        );

        return [
            'url' => $this->getSigningProviderResolver()
                ->resolve($envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE)
                ->getEnvelopeEditReference((string) $remoteEnvelopeId ?? null, $redirectUrl),
        ];
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function getEnvelopeFromStorage(?int $envelopeId): array
    {
        if (null === $envelopeId || null === $envelope = $this->envelopesRepository->findOne($envelopeId)) {
            throw new NotFoundException(\sprintf('The envelope with ID %s is not found', \varToString($envelopeId)));
        }

        return $envelope;
    }
}
