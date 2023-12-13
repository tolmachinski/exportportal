<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Common\DigitalSignature\Provider\ProviderResolverAwareTrait;
use App\Common\DigitalSignature\Provider\ProviderResolverInterface as SigningProviderResolverInterface;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\CreateDigitalDraftMessage;
use App\Envelope\SigningMecahisms;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Generator;

class CreateDigitalDraft
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
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(
        Model $envelopesRepository,
        FileStorageInterface $fileStorage,
        SigningProviderResolverInterface $signingProviderResolver
    ) {
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->setSigningProviderResolver($signingProviderResolver);
    }

    public function __invoke(CreateDigitalDraftMessage $message)
    {
        // Get the envelope draft.
        $envelopeId = $message->getEnvelopeId();
        $envelopeDraft = $this->envelopesRepository->findOne($envelopeId, [
            'with' => [
                'extended_recipients as recipients',
                'documents',
            ],
        ]);
        // Get remote envelope ID (id exists)
        $remoteEnvelopeId = $envelopeDraft['remote_envelope'] ?? null;
        // Basically, we can create digital draft only before processing status.
        // Any onther will result in already saved value.
        // And of course this step must be ommited if we already created the digital draft.
        if (null === $remoteEnvelopeId && EnvelopeStatuses::NOT_PROCESSED === $envelopeDraft['status']) {
            // Get the parts of the envelope
            $signingMechanism = $envelopeDraft['signing_mechanism'] ?? SigningMecahisms::NATIVE;
            $envelopeRecipients = $envelopeDraft['recipients'] ?? new ArrayCollection();
            $envelopeDocuments = $envelopeDraft['documents'] ?? new ArrayCollection();
            // Define some of the envelope options
            $envelopeOptions = [
                'reassign' => false,
                'history'  => true,
            ];

            // Get new digital signing service
            $signingService = $this->getSigningProviderResolver()->resolve($signingMechanism);
            $remoteEnvelopeId = null;
            // If remote signing service found we need to create new digital envelope and store its contents
            if (null !== $signingService) {
                $remoteEnvelopeId = $signingService->createEnvelope(
                    $envelopeDraft,
                    $envelopeRecipients,
                    $this->prepareEnvelopeDocuments($envelopeDocuments, $envelopeDraft['id_sender']),
                    null,
                    $envelopeOptions
                );
            }
            // Update envelope value, even ig it is NULL.
            $this->storeRemoveEnvelopeId($envelopeId, $remoteEnvelopeId);
        }

        return $remoteEnvelopeId;
    }

    /**
     * Prepares documents to be used in the signing provider.
     */
    private function prepareEnvelopeDocuments(Collection $documents, int $senderId): Generator
    {
        foreach ($documents as $document) {
            foreach ($this->fileStorage->getFilesAccessTokens([$document['remote_uuid']], $senderId) as $file => $token) {
                yield $file->withId($document['id']) => $token->withPath(
                    $this->fileStorage->getStoragePathPrefix() . $token->getPath()
                );
            }
        }
    }

    /**
     * Stores the remove envelope ID in the databse.
     */
    private function storeRemoveEnvelopeId(int $envelopeId, ?string $remoteEnvelopeId)
    {
        try {
            $isSaved = (bool) $this->envelopesRepository->updateOne($envelopeId, [
                'remote_envelope' => $remoteEnvelopeId,
            ]);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isSaved) {
            throw new WriteEnvelopeException('Failed to write the "remote_envelope" value into the database', 0, $e ?? null);
        }
    }
}
