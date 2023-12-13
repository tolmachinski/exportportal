<?php

declare(strict_types=1);

namespace App\Envelope\Command;

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Validation\ConstraintList;
use App\Common\Validation\Constraints\ClosureConstraint;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\ValidationException;
use App\Common\Validation\Validator;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Event\SendEnvelopeToRecipients;
use App\Envelope\Event\StartRoutingOrder;
use App\Envelope\Message\SendEnvelopeMessage;
use App\Envelope\Message\SendEnvelopeToRecipientsMessage;
use App\Envelope\Message\StartRoutingOrderMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use ExportPortal\Bridge\Notifier\NotifierAwareTrait;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class SendEnvelope implements CommandInterface
{
    use NotifierAwareTrait;
    use EnvelopeAccessTrait;

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * The envelope workflow steps repository.
     */
    private Model $workflowRepository;

    /**
     * The databse connection.
     */
    private Connection $connection;

    /**
     * Creates instance of the command.
     */
    public function __construct(Model $envelopesRepository, ?NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new Notifier([]);
        $this->connection = $envelopesRepository->getConnection();
        $this->workflowRepository = $envelopesRepository->getRelation('workflowSteps')->getRelated();
        $this->envelopesRepository = $envelopesRepository;
    }

    /**
     * Runs the command.
     */
    public function __invoke(SendEnvelopeMessage $message): void
    {
        $envelope = $this->findEnvelopeInStorage($envelopeId = $message->getEnvelopeId());

        // Check access to envelope.
        $this->assertSenderIsEnvelopeOwner($message->getSenderId(), $envelope);
        $this->assertAccess($envelope);

        //region Updates
        /** @var Collection $recipients */
        $recipients = $envelope['recipients_routing']['recipients'] ?? new ArrayCollection();
        $routingOrder = $envelope['recipients_routing']['next_routing_order'] ?? null;
        $currentRecipients = $envelope['recipients_routing']['next_routing'] ?? new ArrayCollection();

        // Send envelope to the all recipients
        (new SendEnvelopeToRecipients($this->envelopesRepository, $this->notifier))->__invoke(
            (new SendEnvelopeToRecipientsMessage(
                $envelopeId,
                $message->getSenderId(),
                $recipients->map(fn (array $recipient) => ['id' => $recipient['id'], 'user' => $recipient['id_user']])->toArray()
            ))->withAccessRulesList($message->getAccessRulesList())
        );

        // Start envelope routing
        (new StartRoutingOrder($this->envelopesRepository))->__invoke(
            new StartRoutingOrderMessage(
                $envelopeId,
                $routingOrder,
                null,
                \array_column($currentRecipients->toArray(), 'id')
            )
        );
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
    private function assertAccess(array $envelope): void
    {
        // Check if access to the command is granted
        try {
            (new Validator())->assert(new FlatValidationData($envelope), new ConstraintList([
                new ClosureConstraint(
                    fn ($envelope) => \in_array($envelope['status'], [EnvelopeStatuses::CREATED, EnvelopeStatuses::PROCESSED]),
                    sprintf(
                        'The requrest for approval can be sent only for the envelopes in the statuses "%s" and "%s".',
                        EnvelopeStatuses::CREATED,
                        EnvelopeStatuses::PROCESSED
                    )
                ),
                new ClosureConstraint(
                    fn ($envelope) => \in_array($envelope['type'], [EnvelopeTypes::PERSONAL]),
                    sprintf('Routing can be started only for the envelopes of the type "%s".', EnvelopeTypes::PERSONAL)
                ),
            ]));
        } catch (ValidationException $e) {
            // Taking the first constraint violation as the AccessDeniedException message
            throw new AccessDeniedException(
                (\current(\iterator_to_array($e->getValidationErrors()->getIterator())) ?: $e)->getMessage(),
                0,
                $e
            );
        }
    }
}
