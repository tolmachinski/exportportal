<?php

declare(strict_types=1);

namespace App\Services\EditRequest;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\DataProvider\CompanyProvider;
use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Messenger\Message\Event\Company as CompanyEvents;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\Collection;
use Elasticsearch_Users_Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * The service that contains business logic for operations on the profile edit request.
 *
 * @author Anton Zencenco
 */
final class CompanyEditRequestProcessingService extends AbstractEditRequestProcessingService
{
    /**
     * The companies repository.
     */
    protected Model $companiesRepository;

    /**
     * The company data provider.
     */
    protected CompanyProvider $companyDataProvider;

    /**
     * Create instance of processing service.
     *
     * @param Model                               $usersRepository   the users repository
     * @param Model                               $requestRepository the requests repository
     * @param AbstractEditRequestDocumentsService $documentsService  the documents service
     * @param PhoneCodesService                   $phoneCodesService the phone codes service
     * @param MessageBusInterface                 $commandBus        the command message bus
     * @param MessageBusInterface                 $eventBus          the event message bus
     */
    public function __construct(
        Model $usersRepository,
        Model $requestRepository,
        Elasticsearch_Users_Model $elasticsearchUsersModel,
        CompanyProvider $companyDataProvider,
        AbstractEditRequestDocumentsService $documentsService,
        PhoneCodesService $phoneCodesService,
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus
    ) {
        parent::__construct($usersRepository, $requestRepository, $elasticsearchUsersModel, $documentsService, $phoneCodesService, $commandBus, $eventBus);

        $this->companyDataProvider = $companyDataProvider;
        $this->companiesRepository = $companyDataProvider->getRepository();
    }

    /**
     * {Create the company edit request.
     *
     * @param int $companyId the company ID
     */
    public function createRequest(Request $request, int $companyId): int
    {
        $company = $this->companyDataProvider->getCompany($companyId);
        $userId = $company['id_user'];

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
                'id_type'           => $request->request->getInt('type'),
                'id_company'        => $companyId,
                'id_city'           => $request->request->getInt('city') ?: null,
                'id_state'          => $request->request->getInt('region') ?: null,
                'id_country'        => $request->request->getInt('country') ?: null,
                'legal_name'        => $request->request->get('legal_name'),
                'display_name'      => $request->request->get('display_name'),
                'postal_code'       => $request->request->get('postal_code'),
                'longitude'         => $request->request->get('longitude'),
                'latitude'          => $request->request->get('latitude'),
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
        $this->eventBus->dispatch(new CompanyEvents\CreateEditRequestEvent($requestId, $userId, $companyId));
        // Next, if the request has documents we need to send commands that must process their metadata
        if (!$documents->isEmpty()) {
            $documents->map(function (array $document) use ($userId) {
                /**
                 * @var int           $documentId
                 * @var UuidInterface $fileUuid
                 */
                list('id' => $documentId, 'remote_uuid' => $fileUuid) = $document;
                $this->commandBus->dispatch(
                    new CompanyCommands\AcceptTemporaryFile($fileUuid, $documentId, $userId),
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
            'id_type'               => $editRequest['id_type'],
            'id_city'               => $editRequest['id_city'],
            'id_state'              => $editRequest['id_state'],
            'id_country'            => $editRequest['id_country'],
            'id_fax_code_company'   => $editRequest['id_fax_code'],
            'id_phone_code_company' => $editRequest['id_phone_code'],
            'legal_name_company'    => \cleanInput($editRequest['legal_name']),
            'name_company'          => \cleanInput($editRequest['display_name']),
            'address_company'       => \cleanInput($editRequest['address']),
            'latitude'              => \cleanInput($editRequest['latitude']),
            'longitude'             => \cleanInput($editRequest['longitude']),
            'zip_company'           => \cleanInput($editRequest['postal_code']),
            'phone_code_company'    => \cleanInput($editRequest['phone_code_inline']),
            'phone_company'         => \cleanInput($editRequest['phone']),
            'fax_code_company'      => \cleanInput($editRequest['fax_code_inline']),
            'fax_company'           => \cleanInput($editRequest['fax']),
            'updated_company'       => new \DateTimeImmutable(),
        ];
        //endregion Collect data

        //region Update
        // We need to ensure that BOTH records are updated.
        // Failure to do this will lead to multiple side effects.
        // That is why the transactions here are used.
        $connection = $this->requestRepository->getConnection();
        $connection->beginTransaction();
        try {
            if (
                !$this->companiesRepository->updateOne($companyId = $editRequest['id_company'], $update)
                || !$this->requestRepository->updateOne($requestId, [
                    'status'           => EditRequestStatus::ACCEPTED(),
                    'accepted_at_date' => new \DateTimeImmutable(),
                ])
            ) {
                throw new WriteException(\sprintf('Failed to apply edit request for company with ID "%s".', $companyId));
            }
            // Apply changes with documents
            $this->documentsService->applyDocuments($requestId);
            // Commit changes
            $connection->commit();

            $this->elasticsearchUsersModel->sync((int) $editRequest['id_user']);
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }
        //endregion Update

        $this->eventBus->dispatch(new CompanyEvents\AcceptedEditRequestEvent($requestId, $editRequest['id_user'], $companyId));
    }

    /**
     * {@inheritDoc}
     */
    public function declineRequest(array $editRequest, string $reason): void
    {
        // Decline edit request
        parent::declineRequest($editRequest, $reason);
        // Send decline event
        $this->eventBus->dispatch(new CompanyEvents\DeclinedEditRequestEvent($editRequest['id'], $editRequest['id_user'], $editRequest['id_company']));
    }
}
