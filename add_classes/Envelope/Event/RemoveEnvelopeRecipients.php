<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\RemoveRecipientException;
use App\Envelope\Message\RemoveEnvelopeComponentsMessage;
use Exception;

final class RemoveEnvelopeRecipients implements CommandInterface
{
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
    }

    /**
     * {@inheritdoc}
     *
     * @throws RemoveRecipientException if failed to remove the envelope recipients
     */
    public function __invoke(RemoveEnvelopeComponentsMessage $message)
    {
        //region Remove Recipients
        try {
            $isDeleted = (bool) $this->recipientsRepository->deleteAllBy([
                'conditions' => [
                    'envelope' => $message->getEnvelopeId(),
                ],
            ]);
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isDeleted) {
            throw new RemoveRecipientException('Failed to remove the recipients from the database', 0, $e ?? null);
        }
        //endregion Remove Recipients
    }
}
