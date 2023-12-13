<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

use App\Envelope\File\FileInterface;
use App\Envelope\File\ReferenceInterface;
use ArrayAccess;
use SplFileObject;

interface ProviderInterface
{
    /**
     * Gets the envelope by its id.
     *
     * @return null|ArrayAccess|mixed
     */
    public function getEnvelope(?string $envelopeId, ?string $accountId = null, array $params = []);

    /**
     * Gets the document.
     */
    public function getDocument(string $envelopeId, string $documentId, ?string $accountId = null, array $params = []): SplFileObject;

    /**
     * Creates new envelope in the service provider from provided data.
     *
     * @param iterable<FileInterface,ReferenceInterface> $documents
     */
    public function createEnvelope(array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): ?string;

    /**
     * Updates the envelope from draft.
     *
     * @param iterable<FileInterface,ReferenceInterface> $documents
     */
    public function updateEnvelope(?string $envelopeId, array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): ?string;

    /**
     * Removes the envelope.
     */
    public function removeEnvelope(?string $envelopeId, ?string $deleteReason = null, ?string $accountId = null, array $params = []): void;

    /**
     * Gets the envelope public edit URL.
     */
    public function getEnvelopeEditReference(?string $envelopeId, ?string $returnUrl = null, ?string $accountId = null, array $params = []): ?string;

    /**
     * Returns the URL that allows to open preview for envelope.
     */
    public function getEnvelopeRecipientReference(?string $envelopeId, array $recipient, array $params = []): ?string;
}
