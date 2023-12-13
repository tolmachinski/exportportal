<?php

declare(strict_types=1);

namespace App\Services\EditRequest;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Exceptions\AccessDeniedException;
use App\Messenger\Message\Command\Profile as ProfileCommands;
use App\Messenger\Message\Event\Profile as ProfileEvents;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * The service that contains business logic for operations on the profile edit request.
 *
 * @author Anton Zencenco
 */
final class ProfileEditRequestProcessingService extends AbstractEditRequestProcessingService
{
    /**
     * {@inheritDoc}
     */
    public function createRequest(Request $request, int $userId): int
    {
        //region Create
        // First, let's find the proper phone and fax codes
        /** @var CountryCodeInterface $phoneCode */
        $phoneCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('phone_code'))->first() ?: null;
        /** @var CountryCodeInterface $faxCode */
        $faxCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('fax_code'))->first() ?: null;

        // After that we can create the edit request.
        // The method we use returns the newly created edit request with all relevant information.
        // But we don't need the complete request here, just ID value and list of documents (if any).
        /**
         * @var int                                                 $id
         * @var Collection<array{id:int,remove_uuid:UuidInterface}> $documents
         */
        list('id' => $requestId, 'documents' => $documents) = $this->createRequestWithDocuments(
            $userId,
            // Here we collect all basic data we have on the
            // edit request and transform it into the accepted form
            [
                'status'            => EditRequestStatus::PENDING(),
                'id_user'           => $userId,
                'id_city'           => $request->request->getInt('city') ?: null,
                'id_state'          => $request->request->getInt('region') ?: null,
                'id_country'        => $request->request->getInt('country') ?: null,
                'first_name'        => $request->request->get('first_name'),
                'last_name'         => $request->request->get('last_name'),
                'legal_name'        => $request->request->has('has_legal_name') ? $request->request->get('legal_name') : null,
                'update_legal_name' => $request->request->has('has_legal_name'),
                'postal_code'       => $request->request->get('postal_code'),
                'address'           => $request->request->get('address'),
                'reason'            => $request->request->get('reason'),
                'id_phone_code'     => $phoneCode ? $phoneCode->getId() : null,
                'phone_code_inline' => $phoneCode ? $phoneCode->getName() : null,
                'phone'             => $request->request->get('phone'),
                'id_fax_code'       => $faxCode ? $faxCode->getId() : null,
                'fax_code_inline'   => $faxCode ? $faxCode->getName() : null,
                'fax'               => $request->request->get('fax'),
            ],
            // Here we take the document data from request and transform them into accepted format
            \array_map(
                fn (array $document) => \array_merge($document, [
                    'id'   => (int) $document['id'],
                    'uuid' => Uuid::fromString((string) base64_decode($document['document'])),
                ]),
                (array) $request->request->get('documents') ?? []
            )
        );
        //endregion Create

        // Send event about create edit request
        $this->eventBus->dispatch(new ProfileEvents\CreateEditRequestEvent($requestId, $userId));
        // Next, if the request has documents we need to send commands that must process their metadata
        if (!$documents->isEmpty()) {
            $documents->map(function (array $document) use ($userId) {
                /**
                 * @var int           $documentId
                 * @var UuidInterface $fileUuid
                 */
                list('id' => $documentId, 'remote_uuid' => $fileUuid) = $document;
                $this->commandBus->dispatch(
                    new ProfileCommands\AcceptTemporaryFile($fileUuid, $documentId, $userId),
                    [new DelayStamp(3000)]
                );
            });
        }

        return $requestId;
    }

    /**
     * {@inheritDoc}
     */
    public function acceptRequest(array $editRequest): void
    {
        $requestId = $editRequest['id'];
        if ($editRequest['status'] !== EditRequestStatus::PENDING()) {
            throw new AccessDeniedException(
                \sprintf('Only requests in status "%s" can be accpeted', (string) EditRequestStatus::PENDING()),
                10
            );
        }

        //region Collect data
        $update = [
            'fname'         => \cleanInput($editRequest['first_name']),
            'lname'         => \cleanInput($editRequest['last_name']),
            'legal_name'    => null !== $editRequest['legal_name'] ? \cleanInput($editRequest['legal_name']) : '',
            'city'          => $editRequest['id_city'],
            'state'         => $editRequest['id_state'],
            'country'       => $editRequest['id_country'],
            'address'       => \cleanInput($editRequest['address']),
            'phone_code_id' => $editRequest['id_phone_code'],
            'phone_code'    => \cleanInput($editRequest['phone_code']['ccode'] ?? $editRequest['phone_code_inline']),
            'phone'         => \cleanInput($editRequest['phone']),
            'fax_code_id'   => $editRequest['id_fax_code'],
            'fax_code'      => \cleanInput($editRequest['fax_code']['ccode'] ?? $editRequest['fax_code_inline']),
            'fax'           => \cleanInput($editRequest['fax']),
            'zip'           => \cleanInput($editRequest['postal_code']),
        ];
        if (isset($editRequest['city'])) {
            $update['user_city_lat'] = $editRequest['city']['city_lat'];
            $update['user_city_lng'] = $editRequest['city']['city_lng'];
        }
        //endregion Collect data

        //region Update
        // We need to ensure that BOTH records are updated.
        // Failure to do this will lead to multiple side effects.
        // That is why the transactions here are used.
        $connection = $this->requestRepository->getConnection();
        $connection->beginTransaction();
        try {
            if (
                !$this->usersRepository->updateOne($userId = $editRequest['id_user'], $update)
                || !$this->requestRepository->updateOne($requestId, [
                    'status'           => EditRequestStatus::ACCEPTED(),
                    'accepted_at_date' => new \DateTimeImmutable(),
                ])
            ) {
                throw new WriteException(\sprintf('Failed to update profile edit request for user with ID "%s".', $userId));
            }
            // Apply changes with documents
            $this->documentsService->applyDocuments($requestId);
            // Commit changes
            $connection->commit();

            // sync user in elasticsearch
            $this->elasticsearchUsersModel->sync((int) $userId);
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }
        //endregion Update

        // Send accept event
        $this->eventBus->dispatch(new ProfileEvents\AcceptedEditRequestEvent($requestId, $userId));
    }

    /**
     * {@inheritDoc}
     */
    public function declineRequest(array $editRequest, string $reason): void
    {
        // Decline edit request
        parent::declineRequest($editRequest, $reason);
        // Send decline event
        $this->eventBus->dispatch(new ProfileEvents\DeclinedEditRequestEvent($editRequest['id'], $editRequest['id_user']));
    }
}
