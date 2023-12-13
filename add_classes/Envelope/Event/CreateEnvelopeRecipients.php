<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\Message\NewRecipientsMessage;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypesAwareTrait;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Generator;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class CreateEnvelopeRecipients implements CommandInterface
{
    use RecipientTypesAwareTrait;

    /**
     * The users repository.
     */
    private Model $usersRepository;

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
    }

    /**
     * {@inheritdoc}
     *
     * @throws WriteRecipientException if failed to write the envelope recipients
     * @throws NotFoundException       if one of the users was not found
     */
    public function __invoke(NewRecipientsMessage $message)
    {
        $recipients = new ArrayCollection($message->getRecipients());
        $envelopeId = $message->getEnvelopeId();
        $recipientsList = new ArrayCollection();

        //region Assign Users
        $users = $this->getUsersAsRecipients(
            new ArrayCollection(
                array_map(fn ($assignee) => (int) $assignee, array_column($recipients->getValues(), 'assignee'))
            )
        );
        foreach ($users as $recipientOrder => $user) {
            $recipient = $recipients->get($recipientOrder);
            $this->assertValidRecipientType($recipient['type']);

            $recipientsList->add([
                'id_user'          => (int) $user['idu'],
                'id_envelope'      => $envelopeId,
                'type'             => $recipient['type'],
                'due_date'         => $this->normalizeExpirationDate($recipient['expiresAt'] ?? null),
                'status'           => $recipient['completed'] ?? false ? RecipientStatuses::COMPLETED : RecipientStatuses::CREATED,
                'uuid'             => Uuid::uuid6(),
                'routing_order'    => (int) $recipient['order'],
                'assigned_at_date' => new DateTimeImmutable(),
            ]);
        }
        //endregion Assign Users

        //region Write Recipients
        try {
            $isSaved = (bool) $this->recipientsRepository->insertMany($recipientsList->getValues());
        } catch (Exception $e) {
            // Pass - handle below.
        }

        if (!$isSaved) {
            throw new WriteRecipientException('Failed to write the recipients into the database', 0, $e ?? null);
        }
        //endregion Write Recipients
    }

    /**
     * Normalize expiration date.
     *
     * @param DateTimeInterface|string $expirationDate
     */
    private function normalizeExpirationDate($expirationDate, string $format = 'm/d/Y'): ?DateTimeImmutable
    {
        if (empty($expirationDate)) {
            return null;
        }

        if ($expirationDate instanceof DateTimeInterface) {
            if (!$expirationDate instanceof DateTimeImmutable) {
                return DateTimeImmutable::createFromMutable($expirationDate);
            }

            return $expirationDate;
        }

        if (\is_string($expirationDate)) {
            return DateTimeImmutable::createFromFormat($format, $expirationDate);
        }

        throw new InvalidArgumentException(
            \sprintf('The date must be instance of "%s" or string in format"%s"', DateTimeInterface::class, $format)
        );
    }

    /**
     * Returns the usrs that must act as recipients by their IDs.
     *
     * @param Collection<int> $recipients
     *
     * @throws NotFoundException if one of the users was not found
     *
     * @return Generator<int, Array<string, mixed>>
     */
    private function getUsersAsRecipients(Collection $recipients): Generator
    {
        $usersIds = array_unique($recipients->getValues());
        if (empty($usersIds)) {
            return;
        }

        $foundUsers = arrayByKey(
            $this->usersRepository->findAllBy([
                'conditions' => [
                    'filter_by' => [
                        $this->usersRepository->getPrimaryKey() => $usersIds,
                    ],
                ],
            ]),
            $this->usersRepository->getPrimaryKey()
        );

        foreach ($recipients as $index => $userId) {
            if (!isset($foundUsers[$userId])) {
                throw new NotFoundException(
                    sprintf(
                        'The recipient with index "%s" and ID "%s" is not found.',
                        $index,
                        $userId
                    )
                );
            }

            yield $index => $foundUsers[$userId];
        }
    }
}
