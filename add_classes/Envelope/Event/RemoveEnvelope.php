<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Event\RemoveEnvelopeDocuments;
use App\Envelope\Event\RemoveEnvelopeRecipients;
use App\Envelope\Event\RemoveEnvelopeWorkflow;
use App\Envelope\Exception\RemoveDocumentException;
use App\Envelope\Exception\RemoveEnvelopeException;
use App\Envelope\File\StorageInterface as FileStorageInterface;
use App\Envelope\Message\RemoveEnvelopeComponentsMessage;
use App\Envelope\Message\RemoveEnvelopeMessage;
use App\Plugins\EPDocs\ApiAwareTrait;
use Throwable;

final class RemoveEnvelope implements CommandInterface
{
    use ApiAwareTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope workflow steps repository.
     */
    private Model $workflowRepository;

    /**
     * The envelope recipients repository.
     */
    private Model $recipientsRepository;

    /**
     * The envelope documents repository.
     */
    private Model $documentsRepository;

    /**
     * The storage for the document files.
     */
    private FileStorageInterface $fileStorage;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
        $this->envelopesRepository = $envelopesRepository;
        $this->documentsRepository = $envelopesRepository->getRelation('documents')->getRelated();
        $this->recipientsRepository = $envelopesRepository->getRelation('recipients')->getRelated();
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
    }

    /**
     * {@inheritdoc}
     *
     * @throws RemoveDocumentException if failed to remove the envelope
     */
    public function __invoke(RemoveEnvelopeMessage $message)
    {
        try {
            $this->assertEnvelopeExists($envelopeId = $message->getEnvelopeId());
        } catch (NotFoundException $e) {
            return;
        }

        (new RemoveEnvelopeDocuments($this->documentsRepository, $this->fileStorage))->__invoke(new RemoveEnvelopeComponentsMessage($envelopeId));
        (new RemoveEnvelopeRecipients($this->recipientsRepository))->__invoke(new RemoveEnvelopeComponentsMessage($envelopeId));
        (new RemoveEnvelopeWorkflow($this->workflowRepository))->__invoke(new RemoveEnvelopeComponentsMessage($envelopeId));

        //region Remove Documents Records
        try {
            $isDeleted = (bool) $this->envelopesRepository->deleteOne($envelopeId);
        } catch (Throwable $e) {
            // Pass - handle below.
        }

        if (!$isDeleted) {
            throw new RemoveEnvelopeException('Failed to remove the documents from the database.', 0, $e ?? null);
        }
        //endregion Remove Documents Records
    }

    /**
     * Asserts if envelope exists in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function assertEnvelopeExists(?int $envelopeId): void
    {
        if (
            null === $envelopeId
            || null === $this->envelopesRepository->findOne($envelopeId)
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }
    }
}
