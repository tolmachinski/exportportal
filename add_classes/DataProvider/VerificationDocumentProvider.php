<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\NotFoundException;

/**
 * The verification documents data provider service.
 *
 * @author Anton Zencenco
 */
final class VerificationDocumentProvider
{
    /**
     * The documents repository.
     */
    private Model $documentsRepository;

    /**
     * Create the verification documents data provider service class.
     *
     * @param ModelLocator $modelLocator the models locator
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->documentsRepository = $modelLocator->get(\Verification_Documents_Model::class);
    }

    /**
     * Get the document prepared for download.
     *
     * @param null|int $documentId the document ID
     *
     * @throws NotFoundException if document is not found
     */
    public function getDocumentForDownload(?int $documentId): array
    {
        if (
            null === $documentId
            || null === ($document = $this->documentsRepository->findOne($documentId, ['with' => ['type']]))
        ) {
            throw new NotFoundException(sprintf('The user\'s document with ID "%s" is not found', (string) ($documentId ?? 'NULL')));
        }

        return $document;
    }
}
