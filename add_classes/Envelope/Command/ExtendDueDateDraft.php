<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\Message\ExtendDueDatesMessage;
use DateTimeImmutable;
use Exception;

final class ExtendDueDateDraft implements CommandInterface
{
    use EnvelopeAccessTrait;

    /**
     * The users repository.
     */
    private Model $usersRepository;

    /**
     * The envelope repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope recipients repository.
     */
    private Model $recipientsRepository;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $recipientsRepository)
    {
        $this->recipientsRepository = $recipientsRepository;
        $this->usersRepository = $recipientsRepository->getRelation('user')->getRelated();
        $this->envelopesRepository = $recipientsRepository->getRelation('envelope')->getRelated();
    }

    /**
     * {@inheritdoc}
     *
     * @throws WriteRecipientException if failed to update the envelope recipients
     * @throws NotFoundException       if envelope was not found
     */
    public function __invoke(ExtendDueDatesMessage $message)
    {
        $originalEnvelope = $this->findEnvelopeInStorage($message->getEnvelopeId());
        $this->assertEnvelopeIsEditableAdmin($originalEnvelope);

        $recipients = $message->getRecipients();

        //region Update Recipients Due Dates
        try {
            foreach ($recipients as $recipient) {
                if (!empty($recipient['id'])) {
                    $this->recipientsRepository->updateOne($recipient['id'], ['due_date' => DateTimeImmutable::createFromFormat('m/d/Y', $recipient['expiresAt'])]);
                }
            }
        } catch (Exception $e) {
            throw new WriteRecipientException('Failed to update the recipients into the database', 0, $e ?? null);
        }
        //endregion Update Recipients Due Dates
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function findEnvelopeInStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId)
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }
}
