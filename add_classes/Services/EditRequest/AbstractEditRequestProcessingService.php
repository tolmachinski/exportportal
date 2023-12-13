<?php

declare(strict_types=1);

namespace App\Services\EditRequest;

use App\Common\Contracts\Cancel\CancellationRequestStatus;
use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Contracts\Upgrade\UpgradeRequestStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\ArrayCollection;
use Elasticsearch_Users_Model;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * The service that contains abstract business logic for operations on the edit request of different kinds.
 *
 * @author Anton Zencenco
 */
abstract class AbstractEditRequestProcessingService
{
    /**
     * The event bus instance.
     */
    protected MessageBusInterface $eventBus;

    /**
     * The command bus instance.
     */
    protected MessageBusInterface $commandBus;

    /**
     * The phone codes service.
     */
    protected PhoneCodesService $phoneCodesService;

    /**
     * The documents service.
     */
    protected AbstractEditRequestDocumentsService $documentsService;

    /**
     * The local repository with the requests.
     */
    protected Model $requestRepository;

    /**
     * Elasticsearch model for users
     */
    protected Elasticsearch_Users_Model $elasticsearchUsersModel;

    /**
     * The local repository with users.
     */
    protected Model $usersRepository;

    public function __construct(
        Model $usersRepository,
        Model $requestRepository,
        Elasticsearch_Users_Model $elasticsearchUsersModel,
        AbstractEditRequestDocumentsService $documentsService,
        PhoneCodesService $phoneCodesService,
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus
    ) {
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->documentsService = $documentsService;
        $this->requestRepository = $requestRepository;
        $this->phoneCodesService = $phoneCodesService;
        $this->usersRepository = $usersRepository;
        $this->elasticsearchUsersModel = $elasticsearchUsersModel;
    }

    /**
     * Determine if user has pending edit request.
     */
    public function hasPendingRequest(int $userId): bool
    {
        return $this->requestRepository->countAllBy(['scopes' => ['user' => $userId, 'status' => EditRequestStatus::PENDING()]]) > 0;
    }

    /**
     * Determine if profile edit request can be created for the user.
     */
    public function canCreateRequest(int $userId): bool
    {
        // We need check if user is verified.
        // If user is not verified, request cannot be created
        return (bool) $this->usersRepository->countAllBy(['scopes' => ['id' => $userId, 'isVerified' => true]]);
    }

    /**
     * Determine if profile edit request can be accepted.
     */
    public function canAcceptRequest(int $userId): bool
    {
        // First of all, we need to check if request can be created.
        // If not, then request cannot be accepted.
        if (!$this->canCreateRequest($userId)) {
            return false;
        }

        // Otherwise, we need to check if user has no pending upgrade or cancellation requests
        return
            0 === $this->usersRepository
                ->getRelation('upgradeRequests')
                ->getRelated()
                ->countAllBy(['scopes'=> ['user' => $userId, 'status' => UpgradeRequestStatus::FRESH()]])
            && 0 === $this->usersRepository
                ->getRelation('cancellationRequests')
                ->getRelated()
                ->countAllBy(['scopes'=> ['userId' => $userId, 'status' => CancellationRequestStatus::INIT()]])
        ;
    }

    /**
     * Create the edit request.
     *
     * @param int $recordId the ID of the record we create request for
     */
    abstract public function createRequest(Request $request, int $recordId): int;

    /**
     * Accept the edit request.
     *
     * @throws AccessDeniedException when trying to apply request in invalid status
     * @throws NotFoundException     when edit request is not found
     * @throws ProcessingException   when one or more documents for request are not yet processed
     * @throws OutOfBoundsException  when at least one of the required documents is missing for edit request
     * @throws WriteException        when failed to write updates
     */
    abstract public function acceptRequest(array $editRequest): void;

    /**
     * Decline the edit request.
     *
     * @param string $reason the decline reason
     */
    public function declineRequest(array $editRequest, string $reason): void
    {
        //region Decline
        $requestId = $editRequest['id'];
        if ($editRequest['status'] !== EditRequestStatus::PENDING()) {
            throw new AccessDeniedException(
                \sprintf('Only requests in status "%s" can be declined', (string) EditRequestStatus::PENDING()),
            );
        }
        if (
            !$this->requestRepository->updateOne($requestId, [
                'status'           => EditRequestStatus::DECLINED(),
                'decline_reason'   => $reason,
                'declined_at_date' => new \DateTimeImmutable(),
            ])
        ) {
            throw new WriteException(
                \sprintf('Failed to decline the edit request "%s"', $requestId)
            );
        }
        //endregion Decline
    }

    /**
     * Creates the edit request with documents.
     *
     * @param int   $userId      the ID of the user that creates the request
     * @param array $editRequest the base edit request data
     * @param array $documents   the list of the documents
     *
     * @throws WriteException   when failed to write the edit request into the storage
     * @throws \LengthException when the amount of added documents is not the same as the amount of exisitng ones
     *
     * @return array the newly created edit request
     */
    protected function createRequestWithDocuments(int $userId, array $editRequest, array $documents = []): array
    {
        // We need to ensure that BOTH records (add request and add documents) are created.
        // Failure to do this will lead to multiple side effects.
        // That is why the transactions here are used.
        $connection = $this->requestRepository->getConnection();
        $connection->beginTransaction();
        try {
            if (!($requestId = (int) $this->requestRepository->insertOne($editRequest))) {
                throw new WriteException(\sprintf('Failed to add profile edit request for user with ID "%s".', $userId));
            }
            // Apply changes with documents
            $savedDocuments = $this->documentsService->addDocuments($requestId, $userId, \array_map(
                fn (array $document) => \array_merge($document, [
                    'id'   => (int) $document['id'],
                    'uuid' => Uuid::fromString((string) base64_decode($document['document'])),
                ]),
                $documents
            ));
            // Commit changes
            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }

        // Here we return the updated edit request object with added documents and the request ID
        // values
        return \array_merge($editRequest, [
            'id'        => $requestId,
            'documents' => new ArrayCollection(
                \array_map(
                    fn (array $tuple) => ['id' => $tuple['id'], 'remote_uuid' => $tuple['uuid']],
                    $savedDocuments
                )
            ),
        ]);
    }
}
