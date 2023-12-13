<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Validation\ConstraintList;
use App\Common\Validation\Constraints\ClosureConstraint;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\ValidationException;
use App\Common\Validation\Validator;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\Bridge\Order\Message\RequireEnvelopeApprovalMessage;
use App\Envelope\Command\CommandInterface;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Exception\EnvelopeException;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\HistoryAwareTrait;
use App\Envelope\HistoryEvent;
use App\Envelope\SigningMecahisms;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Throwable;

final class RequireEnvelopeApproval implements CommandInterface
{
    use HistoryAwareTrait;
    use NotifierAwareTrait;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * Creates instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->envelopesRepository = $envelopesRepository;
        $this->setHistoryRepository($envelopesRepository->getRelation('history')->getRelated());
    }

    /**
     * Runs the command.
     */
    public function __invoke(RequireEnvelopeApprovalMessage $message): void
    {
        // Check access to the envelope
        $this->assertAccess(
            // Get the envelope
            $envelope = $this->findEnvelopeInStorage($envelopeId = $message->getEnvelopeId()),
            $message->getSenderId()
        );

        //region Send request
        $this->connection->beginTransaction();

        try {
            //region Update envelope status
            $isUpdated = $this->envelopesRepository->updateOne($envelopeId, [
                'status'                 => EnvelopeStatuses::NOT_PROCESSED,
                'status_changed_at_date' => new DateTimeImmutable(),
            ]);
            if (!$isUpdated) {
                throw new WriteEnvelopeException('Failed to write the envelope into database', 0);
            }
            //endregion Update envelope status

            //region Write history
            $this->addHistoryEvent(new HistoryEvent(HistoryEvent::REQUIRE_PROCESSING), $envelopeId, $message->getSenderId(), ['isInternal' => true]);
            //endregion Write history

            //region Notify
            if (!empty($message->getAccessRulesList())) {
                $sender = $envelope['sender'] ?? null;
                $orderId = $envelope['order_reference']['id_order'];

                $this->notifier->send(
                    new OrderDocumentEnvelopeNotification(
                        Type::REQUIRED_ENVELOPE_APPROVAL,
                        $orderId,
                        $envelopeId,
                        $envelope['display_title'],
                        null === $sender ? null : new Sender(
                            $sender['id'],
                            $sender['full_name'],
                            $sender['legal_name'],
                            $sender['group_type']
                        ),
                        [(string) SystemChannel::STORAGE()]
                    ),
                    new RightfulRecipient($message->getAccessRulesList())
                );
            }
            //endregion Notify

            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            if ($e instanceof EnvelopeException) {
                throw $e;
            }

            throw new EnvelopeException('Failed to send envelope processing request.', 0, $e);
        }
        //endregion Send request
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
            || null === $envelope = $this->envelopesRepository->findOneBy([
                'conditions' => ['id' => $envelopeId],
                'with'       => [
                    'order_reference',
                    'extended_sender as sender',
                    'recipients as recipients_routing' => function (RelationInterface $relation) {
                        $relation
                            ->getQuery()
                            ->orderBy('routing_order', 'ASC')
                        ;
                    },
                ],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Checks access to the envelope.
     *
     * @throws AccessDeniedException if access is not granted
     */
    private function assertAccess(array $envelope, int $senderId): void
    {
        // Check if access to the command is granted
        try {
            (new Validator())->assert(new FlatValidationData($envelope), new ConstraintList([
                new ClosureConstraint(
                    fn ($envelope) => EnvelopeStatuses::CREATED === $envelope['status'],
                    sprintf('The requrest for approval can be sent only for the envelopes in the status "%s".', EnvelopeStatuses::CREATED)
                ),
                new ClosureConstraint(
                    fn ($envelope) => $senderId === $envelope['id_sender'],
                    'Only sender can edit this envelope.'
                ),
                new ClosureConstraint(
                    fn ($envelope) => EnvelopeTypes::PERSONAL === $envelope['type'],
                    sprintf('The requrest for approval can be sent only for the envelopes of the type "%s".', EnvelopeTypes::PERSONAL)
                ),
                new ClosureConstraint(
                    fn ($envelope) => SigningMecahisms::NATIVE !== $envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE,
                    'The requrest for approval can be sent only for the envelopes of the type "%s".'
                ),
            ]));
        } catch (ValidationException $e) {
            throw new AccessDeniedException('The access to the operation is not granted', 0, $e);
        }
    }
}
