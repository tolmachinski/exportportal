<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\AccessDocumentMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class AccessDocument implements CommandInterface
{
    use EnvelopeAccessTrait;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * The envelope documents repository.
     */
    private Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * The REST API host.
     */
    private string $apiHost;

    /**
     * The flag that indicates if access to the document must be always authorized.
     */
    private bool $alwaysAuthorize;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $documentsRepository, FileStorageInterface $fileStorage, bool $alwaysAuthorize = true)
    {
        $this->connection = $documentsRepository->getConnection();
        $this->fileStorage = $fileStorage;
        $this->alwaysAuthorize = $alwaysAuthorize;
        $this->documentsRepository = $documentsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(AccessDocumentMessage $message)
    {
        //region Serial
        $userId = $message->getSenderId();
        $document = $this->getDocumentFromStorage($message->getEnvelopeId(), $message->getDocumentId());
        if (empty($document)) {
            throw new NotFoundException('The documents for request are not found.');
        }

        $envelope = $document['envelope'];
        $recipients = $document['recipients'] ?? new ArrayCollection();
        $currentRecipient = null;

        if ($this->alwaysAuthorize) {
            if ((int) $envelope['id_sender'] !== $userId) {
                $currentRecipient = $recipients->filter(fn (array $recipient) => $userId === $recipient['id_user'])->first() ?: null;
                if (null === $currentRecipient) {
                    throw new AccessDeniedException('Only sender and recipient can access the documents.');
                }
            }
        }

        $slugger = new AsciiSlugger();
        $envelopeSlug = $slugger->slug($envelope['display_title'])->lower()->snake();
        $foundTokens = [];
        /** @var iterable<FileObject, AccessTokenObject> */
        $acessTokens = $this->fileStorage->getFilesAccessTokens([$document['remote_uuid']], $userId, $message->getTtl());
        foreach ($acessTokens as $file => $acessToken) {
            $foundTokens[] = [
                'slug'      => $envelopeSlug,
                'name'      => $file->getName(),
                'path'      => $acessToken->getPath(),
                'preview'   => $acessToken->getPreviewPath(),
                'fullname'  => "{$file->getName()}.{$file->getExtension()}",
                'extension' => $file->getExtension(),
            ];
        }

        return \current($foundTokens);
    }

    /**
     * Get document from storage.
     */
    protected function getDocumentFromStorage(?int $envelopeId, ?int $documentId): array
    {
        if (
            null === $envelopeId
            || null === $documentId
            || null === $document = $this->documentsRepository->findOne($documentId, [
                'with'       => ['envelope', 'recipients'],
                'conditions' => [
                    'envelope'  => $envelopeId,
                ],
            ])
        ) {
            throw new NotFoundException(
                sprintf(
                    'The document with ID %s for envelope %s is not found',
                    varToString($documentId),
                    varToString($envelopeId)
                )
            );
        }

        return $document;
    }
}
