<?php

declare(strict_types=1);

namespace App\Common\DigitalSignature\Provider;

use SplFileObject;
use SplTempFileObject;

class NullProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEnvelope(?string $envelopeId, ?string $accountId = null, array $params = [])
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $envelopeId, string $documentId, ?string $accountId = null, array $params = []): SplFileObject
    {
        return new SplTempFileObject();
    }

    /**
     * {@inheritdoc}
     */
    public function createEnvelope(array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateEnvelope(?string $envelopeId, array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeEnvelope(?string $envelopeId, ?string $deleteReason = null, ?string $accountId = null, array $params = []): void
    {
        // Hic svnt dracones
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvelopeEditReference(?string $envelopeId, ?string $returnUrl = null, ?string $accountId = null, array $params = []): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvelopeRecipientReference(?string $envelopeId, array $recipient, array $params = []): ?string
    {
        return null;
    }
}
