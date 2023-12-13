<?php

declare(strict_types=1);

use App\Common\Contracts\Notifier\SystemChannel;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\DigitalSignature\Provider\DocuSign\DocuSign;
use App\Envelope\Bridge\EPDocs\FileStorage;
use App\Envelope\Bridge\Notifier\Notification\OrderDocumentEnvelopeNotification;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeWorkflowStatuses;
use App\Envelope\Event\BindDocumentsAndRecipients;
use App\Envelope\Event\HideOutdatedDocuments;
use App\Envelope\Event\RemoveDocumentFiles;
use App\Envelope\Exception\WriteDocumentException;
use App\Envelope\Exception\WriteEnvelopeException;
use App\Envelope\Exception\WriteRecipientException;
use App\Envelope\Exception\WriteWorkflowException;
use App\Envelope\File\StorageInterface;
use App\Envelope\HistoryEvent;
use App\Envelope\Message\BindDocumentsAndRecipientsMessage;
use App\Envelope\Message\HideOutdatedDocumentsMessage;
use App\Envelope\Message\RemoveDocumentFilesMessage;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Envelope\WorkflowStepStatuses;
use App\Envelope\WorkflowStepTypes;
use App\Plugins\EPDocs\Credentials\JwtCredentials;
use App\Plugins\EPDocs\Http\Auth;
use App\Plugins\EPDocs\Http\Authentication\Bearer;
use App\Plugins\EPDocs\Rest\RestClient;
use App\Plugins\EPDocs\Storage\JwtTokenStorage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DocuSign\eSign\Model as DocuSignModels;
use DocuSign\eSign\Model\Agent;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\CertifiedDelivery;
use DocuSign\eSign\Model\Editor;
use DocuSign\eSign\Model\InPersonSigner;
use DocuSign\eSign\Model\Intermediary;
use DocuSign\eSign\Model\ModelInterface;
use DocuSign\eSign\Model\Notary;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Witness;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use GuzzleHttp\Client;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * Controller DocuSign.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class DocuSign_Controller extends TinyMVC_Controller
{
    /**
     * The logger.
     */
    private LoggerInterface $monoLogger;

    /**
     * The envelopes model.
     */
    private Envelopes_Model $envelopes;

    /**
     * The app file storage.
     */
    private StorageInterface $fileStorage;

    /**
     * The notifier.
     */
    private NotifierInterface $notifier;

    /**
     * The DocuSignProvider.
     */
    private DocuSign $docusignProvider;

    /**
     * The map of the supported algorithms.
     */
    private array $supportedAlgorithms = [
        'HMACSHA256' => 'sha256',
    ];

    /**
     * The list of supported recipients types.
     */
    private array $supportedRecipientTypes = [
        Signer::class,
        CertifiedDelivery::class,
    ];

    /**
     * Listen for the DocuSign requests.
     */
    public function listen(): Response
    {
        $request = request();
        $logger = $this->getLogger();

        // Log the incomming request
        $logger->debug(sprintf('Received the request from origin: %s', $request->headers->get('Origin') ?? 'http://localhost'));

        // Verify the request if HMAC signatures are enabled
        if (filter_var(config('env.DOCUSIGN_CONNECT_ENABLE_HMAC', FILTER_VALIDATE_BOOL))) {
            try {
                $this->verifyConnectRequestWithHmac($request);
            } catch (AccessDeniedException $e) {
                // Log the exception
                $logger->error(sprintf('PHP Exception %s: %s', get_class($e), $e->getMessage()), ['exception' => $e]);
                // Get out of here!
                return $this->denyAccess();
            }
        }
        $logger->info('The incomming request is authorized. Proceeding with the update.');

        try {
            try {
                $logger->debug('Decoding the request payload.');
                // Decode the request payload
                // and create an envelope model from it
                /** @var DocuSignModels\Envelope $digitalEnvelope */
                $digitalEnvelope = $this
                    ->getDocusignProvider()
                    ->getApiClient()
                    ->getSerializer()
                    ->deserialize(
                        $decodedRequestBody = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR),
                        DocuSignModels\Envelope::class,
                        ['Content-Disposition' => $request->headers->get('Content-Disposition')]
                    )
                ;
            } catch (JsonException $e) {
                // Log the exception
                $logger->error(sprintf('PHP Exception %s: %s', get_class($e), $e->getMessage()), ['exception' => $e]);
                // Get out of here!
                return $this->serverError();
            }

            $logger->debug('Getting the local envelope.', ['digitalEnvelopeId' => $digitalEnvelope->getEnvelopeId()]);
            if ($this->updateEnvelope($digitalEnvelope, $this->findLocalEnvelope(Uuid::fromString($digitalEnvelope->getEnvelopeId())), $decodedRequestBody)) {
                $logger->info('The envelope is successfully synchronized.', ['digitalEnvelopeId' => $digitalEnvelope->getEnvelopeId()]);
            } else {
                $logger->info('Nothing to synchronize - envelope is up to date.', ['digitalEnvelopeId' => $digitalEnvelope->getEnvelopeId()]);
            }
        } catch (Throwable $e) {
            // Log the exception
            $logger->error(sprintf('PHP Exception %s: %s', get_class($e), $e->getMessage()), ['exception' => $e]);

            // Get out of here!
            if (404 === $e->getCode()) {
                return $this->notFound();
            }
            if (403 === $e->getCode()) {
                return $this->denyAccess();
            }

            return $this->serverError();
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Gets the envelopes model.
     */
    private function getEnvelopes(): Envelopes_Model
    {
        return $this->envelopes = ($this->envelopes ?? model(Envelopes_Model::class));
    }

    /**
     * Gets the file storage.
     */
    private function getFileStorage(): StorageInterface
    {
        return $this->fileStorage = ($this->fileStorage ?? new FileStorage(
            $this->getDocumentsApiClient(),
            config('env.EP_DOCS_REFERRER', 'http://localhost'),
            config('env.EP_DOCS_ADMIN_SALT'),
            library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
        ));
    }

    /**
     * Gets the DocuSign providers.
     *
     * @throws DomainException if the provider is not configured properly or empty
     */
    private function getDocusignProvider(): DocuSign
    {
        if (!isset($this->docusignProvider)) {
            /** @var TinyMVC_Library_Digital_Signatures $digitalSignatures */
            $digitalSignatures = library(TinyMVC_Library_Digital_Signatures::class);
            /** @var DocuSign $docusign */
            $docusign = $digitalSignatures->resolve('docusign');
            if (null === $docusign) {
                throw new DomainException('The DocuSign provider is not configured properly or empty.');
            }
            $this->docusignProvider = $docusign;
        }

        return $this->docusignProvider;
    }

    /**
     * Gets a logger.
     */
    private function getLogger(): LoggerInterface
    {
        if (!isset($this->monoLogger)) {
            $logger = new Logger('docusign.connect');
            $logger->pushHandler(new RotatingFileHandler(\App\Common\LOGS_PATH . '/docusign/connect-error.log', 10, Logger::ERROR));
            $logger->pushHandler(
                new FilterHandler(
                    new RotatingFileHandler(\App\Common\LOGS_PATH . '/docusign/connect.log', 10, Logger::DEBUG),
                    Logger::DEBUG,
                    Logger::WARNING
                ),
            );
            $logger->pushProcessor(new IntrospectionProcessor(Logger::WARNING));
            $logger->pushProcessor(function (array $record): array {
                list('request' => $request, 'exception' => $exception) = $record['context'] ?? [];

                // Process exception
                if ($exception instanceof Throwable) {
                    $record['extra']['exception'] = [
                        'class'   => get_class($exception),
                        'line'    => $exception->getLine(),
                        'code'    => $exception->getCode(),
                        'message' => $exception->getMessage(),
                    ];

                    unset($record['context']['exception']);
                }

                // Process request
                if ($request instanceof Request) {
                    $record['extra']['request'] = [
                        'class'    => get_class($request),
                        'contents' => $request->getContent(),
                        'headers'  => $request->headers->all(),
                        'params'   => $request->request->all(),
                        'query'    => $request->query->all(),
                    ];

                    unset($record['context']['request']);
                }

                return $record;
            });

            $this->monoLogger = $logger;
        }

        return $this->monoLogger;
    }

    /**
     * Returns the prepared 404 json response.
     */
    private function notFound(): Response
    {
        return new JsonResponse(['error' => ['code' => 'not_found', 'message' => 'The respurce is not found.']], 404);
    }

    /**
     * Returns the prepared 403 json response.
     */
    private function denyAccess(): Response
    {
        return new JsonResponse(['error' => ['code' => 'access_denied', 'message' => 'The access is denied']], 403);
    }

    /**
     * Returns the prepared 500 json response.
     */
    private function serverError(): Response
    {
        return new JsonResponse(['error' => ['code' => 'server_error', 'message' => 'The request processing failed']], 500);
    }

    /**
     * Finds local envelope data by provided digital envelope UUID.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function findLocalEnvelope(UuidInterface $digitalEnvelopeId): array
    {
        if (
            null === $localEnvelope = $this->getEnvelopes()->findOneBy([
                'with'       => [
                    'extended_recipients as recipients_routing',
                    'extended_sender as sender',
                    'workflow_steps as workflow',
                    'order_reference',
                    'documents',
                ],
                'conditions' => [
                    'remote_envelope' => (string) $digitalEnvelopeId,
                ],
            ])
        ) {
            throw new NotFoundException(
                sprintf('The digital envelope with UUID "%s" is not found in the local storage.', $digitalEnvelopeId),
                404
            );
        }

        return $localEnvelope;
    }

    /**
     * Verifies the DocuSign Connect request.
     */
    private function verifyConnectRequestWithHmac(Request $request): void
    {
        // Get the DocuSign Connect keys
        $connectKeys = array_filter(
            [config('env.DOCUSIGN_CONNECT_SECRET_1'), config('env.DOCUSIGN_CONNECT_SECRET_2'), config('env.DOCUSIGN_CONNECT_SECRET_3')],
            fn (?string $key) => null !== $key
        );
        // If list of the keys is empty
        // then we don't need to verify the request
        if (empty($connectKeys)) {
            return;
        }

        // Get the DocuSign account keys
        $accountId = config('env.DOCUSIGN_ACCOUNT_ID');
        // If request comes with different account ID,
        // then just leave it.
        if ($accountId !== $request->headers->get('X-DocuSign-AccountId')) {
            throw new AccessDeniedException('The request is sent for invalid or unknown account.', 403);
        }

        // Get the hash algo from the request headers
        $hashType = $request->headers->get('X-Authorization-Digest');
        // And check if we support it
        if (!isset($this->supportedAlgorithms[$hashType])) {
            throw new AccessDeniedException(sprintf('The hash algorithm of the type "%s" is not supported', $hashType), 403);
        }

        // Verify the request payload
        $requestPayload = $request->getContent();
        foreach ($connectKeys as $index => $secretKey) {
            // First of all, we ned to get the signature from headers
            // Each kyey has corresponding signature
            $headerName = 'X-DocuSign-Signature-' . ($index + 1);
            if (!$request->headers->has($headerName)) {
                throw new AccessDeniedException(sprintf('The required header "%s" is missing from request', $headerName), 403);
            }
            $signature = $request->headers->get($headerName);
            if (empty($signature)) {
                throw new AccessDeniedException(sprintf('The value of the MAC signature in request header "%s" is empty.', $headerName), 403);
            }

            // After that we compute hash using the request body and secret key
            $computedHash = base64_encode(hex2bin(hash_hmac($this->supportedAlgorithms[$hashType], $requestPayload, utf8_encode($secretKey))));
            // If hashes are not equals, then request is a fraud
            if (!hash_equals($signature, $computedHash)) {
                throw new AccessDeniedException(sprintf('The MAC signature from the request header "%s" didn\'t pass the HMAC verififcation.', $headerName), 403);
            }
        }
    }

    /**
     * Updates the envelope. Returns **true** when at least something updated.
     */
    private function updateEnvelope(DocuSignModels\Envelope $digitalEnvelope, array $currentEnvelope, object $decodedRequestBody): bool
    {
        // Prepare history pool.
        $historyPool = new ArrayCollection();

        //region Prepare recipients update
        $recipientsForSync = $this->prepareRecipientsForSynchronization(
            $currentEnvelope,
            $recipientsList = arrayCollapse(
                array_map(
                    fn (array $recipient) => [(string) $recipient['uuid'] => $recipient],
                    iterator_to_array($currentEnvelope['recipients_routing']['recipients'] ?? new ArrayCollection())
                )
            ),
            $digitalRecipientsList = $this->flattenRecipients($digitalEnvelope->getRecipients(), true),
            $historyPool
        );
        //endregion Prepare recipients update

        //region Prepare workflow update
        $workflowStepsForSync = $this->prepareWorkflowStepsForSynchronization(
            $currentEnvelope,
            $digitalRecipientsList,
            (int) $digitalEnvelope->getRecipients()->getCurrentRoutingOrder()
        );
        //endregion Prepare workflow update

        //region Prepare envelope
        $envelopeId = $currentEnvelope['id'];
        $envelopeForSync = $this->prepareEnvelopeForSynchronization(
            $currentEnvelope,
            $digitalEnvelope,
            $workflowStepsForSync,
            $historyPool
        );
        //endregion Prepare envelope

        //region Prepare document reader
        $documentReader = $this->preprareDocumentReaderForSynchronization(
            $envelopeForSync['status'] ?? null,
            array_map(
                fn (string $key, array $recipient) => $this->getListDiff($recipient, $recipientsList[$key] ?? []),
                array_keys($recipientsForSync),
                $recipientsForSync
            ),
            $digitalEnvelope,
            $decodedRequestBody
        );
        //endregion Prepare document reader

        //region Update all
        $hasUpdates = false;
        $storage = $this->getFileStorage();
        $envelopes = $this->getEnvelopes();
        $connection = $envelopes->getConnection();
        /** @var Envelope_Recipients_Model $recipients */
        $recipients = model(Envelope_Recipients_Model::class);
        /** @var Envelope_Workflow_Steps_Model $workflowSteps */
        $workflowSteps = model(Envelope_Workflow_Steps_Model::class);
        /** @var Envelope_Documents_Model $documents */
        $documents = model(Envelope_Documents_Model::class);
        $connection->beginTransaction();

        try {
            //region Update workflow
            list($updatedWorkflowSteps, $newWorkflowSteps) = $workflowStepsForSync->partition(fn ($key, array $step) => isset($step['id']));
            if (!$updatedWorkflowSteps->isEmpty()) {
                /** @var Collection $originalSteps */
                $originalSteps = $currentEnvelope['workflow']['steps'] ?? new ArrayCollection();
                foreach ($updatedWorkflowSteps as $stepUuid => $step) {
                    $stepUpdate = $this->getListDiff($step, $originalSteps->get($stepUuid) ?? []);
                    if (empty($stepUpdate)) {
                        continue;
                    }

                    if (!$workflowSteps->updateOne($step['id'], $stepUpdate)) {
                        throw new WriteWorkflowException(
                            sprintf('Failed to update the envelope workflow step "%s"', $step['uuid'])
                        );
                    }

                    $hasUpdates = true;
                }
            }
            if (!$newWorkflowSteps->isEmpty()) {
                if (!(bool) $workflowSteps->insertMany($newWorkflowSteps->toArray())) {
                    throw new WriteWorkflowException(
                        sprintf('Failed to create new envelope workflow step for envelope "%s"', $currentEnvelope['id'])
                    );
                }
                $envelopeForSync['current_workflow_step'] = $newWorkflowSteps->last()['uuid'] ?? null;
                $hasUpdates = true;
            }
            //endregion Update workflow

            //region Update recipients
            $updatedRecipients = [];
            if (!empty($recipientsForSync)) {
                foreach ($recipientsForSync as $key => $recipient) {
                    $recipientUpdate = $this->getListDiff($recipient, $recipientsList[$key] ?? []);
                    if (empty($recipientUpdate)) {
                        continue;
                    }

                    // Collecting updated recipients. We will use them later.
                    $updatedRecipients[] = $recipient;
                    if (!$recipients->updateOne($recipient['id'], $recipientUpdate)) {
                        throw new WriteRecipientException(
                            sprintf('Failed to update the envelope recipient with ID "%s"', $recipient['id'])
                        );
                    }

                    $hasUpdates = true;
                }
            }
            //endregion Update recipients

            //region Add document
            if (null !== $documentReader) {
                $routing = $currentEnvelope['recipients_routing'];
                $parentEnvelopeId = $currentEnvelope['id_parent_document'] ?? null;
                /** @var Collection $lastRouting */
                $lastRouting = $routing['last_routing'];
                $boundRecipients = ($routing['recipients'] ?? new ArrayCollection())->map(fn (array $recipient) => ['id' => $recipient['id']])->toArray();
                $boundAssignees = array_column($recipientsForSync, 'id_user');
                if ($routing['last_routing_order'] === $routing['current_routing_order']) {
                    $boundAssignees = array_unique([...$boundAssignees, ...array_column($lastRouting->toArray(), 'id_user')]);
                }

                $file = $storage->createFile(
                    $currentEnvelope['id_sender'],
                    $documentReader(),
                    "combined_document_envelope_{$envelopeId}_{$routing['current_routing_order']}.pdf",
                    'document',
                    $boundAssignees
                );
                $documentId = $documents->insertOne([
                    'id_envelope'            => $envelopeId,
                    'id_parent_document'     => $parentEnvelopeId,
                    'uuid'                   => $documentUuid = Uuid::uuid6(),
                    'label'                  => 'copy',
                    'file_size'              => $file->getSize(),
                    'file_name'              => $file->getName(),
                    'file_extension'         => $file->getExtension(),
                    'file_original_name'     => $file->getOriginalName(),
                    'mime_type'              => $file->getType(),
                    'display_name'           => $file->getOriginalName(),
                    'internal_name'          => sprintf('E%s-F-%s', $envelopeId, base64_encode((string) $documentUuid)),
                    'remote_uuid'            => $file->getUuid(),
                    'is_final_external_copy' => true,
                ]);
                if (!$documentId) {
                    throw new WriteDocumentException(
                        sprintf('Failed to write the document for envelope "%s" into database.', $envelopeId)
                    );
                }

                // Hide outdated documents for recipients
                (new HideOutdatedDocuments($documents))->__invoke(
                    new HideOutdatedDocumentsMessage($envelopeId, \array_column($boundRecipients, 'id'))
                );
                // Bind recipients to the documents
                (new BindDocumentsAndRecipients($documents))->__invoke(
                    new BindDocumentsAndRecipientsMessage($envelopeId, [(int) $documentId], \array_column($boundRecipients, 'id'))
                );

                $hasUpdates = true;
            }
            //endregion Add document

            //region Update envelope
            if (!empty($envelopeUpdate = $this->getListDiff($envelopeForSync, $currentEnvelope))) {
                if (!$envelopes->updateOne($envelopeId, $envelopeUpdate)) {
                    throw new WriteEnvelopeException(
                        sprintf('Failed to update the envelope with ID "%s"', $envelopeId)
                    );
                }

                $hasUpdates = true;
            }
            //endregion Update envelope

            $connection->commit();
        } catch (Throwable $e) {
            // Rollback changes
            $connection->rollBack();
            if (isset($file)) {
                (new RemoveDocumentFiles($storage))(new RemoveDocumentFilesMessage([(string) $file->getUuid()]));
            }

            throw $e;
        }
        //endregion Update all

        //region Notifications & history
        if ($hasUpdates) {
            $this->writeEnvelopeHistory($envelopeId, $historyPool);
            $this->sendEnvelopeNotifications(
                $this->getContainer()->get(NotifierInterface::class),
                $currentEnvelope,
                $currentEnvelope['recipients_routing']['recipients'] ?? new ArrayCollection(),
                !empty($envelopeUpdate) ? $envelopeUpdate : null,
                $updatedRecipients
            );
        }
        //endregion Notifications & history

        return $hasUpdates;
    }

    /**
     * Returns the envelope with the data that must be synchronized.
     */
    private function prepareEnvelopeForSynchronization(
        array $currentEnvelope,
        DocuSignModels\Envelope $digitalEnvelope,
        Collection $workflowSteps,
        Collection $historyPool
    ): array {
        $date = fn (?string $dateString) => null === $dateString ? null : new DateTimeImmutable($dateString);

        // Re-assign dates
        $currentEnvelope['sent_at_date'] = $currentEnvelope['sent_at_date'] ?? $date($digitalEnvelope->getSentDateTime() ?: null);
        $currentEnvelope['processed_at_date'] = $currentEnvelope['processed_at_date'] ?? $date($digitalEnvelope->getSentDateTime() ?: null);
        $currentEnvelope['completed_at_date'] = $currentEnvelope['completed_at_date'] ?? $date($digitalEnvelope->getCompletedDateTime() ?: null);
        $currentEnvelope['declined_at_date'] = $currentEnvelope['declined_at_date'] ?? $date($digitalEnvelope->getDeclinedDateTime() ?: null);
        $currentEnvelope['voided_at_date'] = $currentEnvelope['voided_at_date'] ?? $date($digitalEnvelope->getVoidedDateTime() ?: null);
        $currentEnvelope['deleted_at_date'] = $currentEnvelope['deleted_at_date'] ?? $currentEnvelope['voided_at_date'];
        $currentEnvelope['sent_original_at_date'] = $currentEnvelope['sent_original_at_date'] ?? $date($digitalEnvelope->getInitialSentDateTime() ?: null);

        // We need to analize all incomming statuses and make all appropriate changes.
        switch ($digitalEnvelope->getStatus()) {
            case 'sent':
                if (EnvelopeStatuses::SENT !== $currentEnvelope['status']) {
                    if (EnvelopeStatuses::NOT_PROCESSED === $currentEnvelope['status']) {
                        // Basically, giving our current workflow, the envelope must be sent from admin panel.
                        // Yes, we have there an endpoint for recieving the response from DocuSign, but
                        // it can fail for different reasont, for example, bad internet connection.
                        // That is why this place will act as failsafe for this process and will commit edit mode.
                        $currentEnvelope['status'] = EnvelopeStatuses::PROCESSED;
                        $currentEnvelope['processed_at_date'] = $currentEnvelope['processed_at_date'] ?? new DateTimeImmutable();

                        $historyPool->add([new HistoryEvent(HistoryEvent::PROCESS), null, $currentEnvelope['processed_at_date'], true]);
                    }

                    $currentEnvelope['status'] = EnvelopeStatuses::SENT;
                    $currentEnvelope['sent_at_date'] = $currentEnvelope['sent_at_date'] ?? new DateTimeImmutable();
                    $currentEnvelope['sent_original_at_date'] = $currentEnvelope['sent_original_at_date'] ?? new DateTimeImmutable();
                    $currentEnvelope['status_changed_at_date'] = $currentEnvelope['sent_at_date'];

                    $historyPool->add([new HistoryEvent(HistoryEvent::SEND), $currentEnvelope['id_sender'], $currentEnvelope['sent_at_date']]);
                }

                break;
            case 'completed':
                if (EnvelopeStatuses::COMPLETED !== $currentEnvelope['status']) {
                    $currentEnvelope['status'] = EnvelopeStatuses::COMPLETED;
                    $currentEnvelope['completed_at_date'] = $currentEnvelope['completed_at_date'] ?? new DateTimeImmutable();
                    $currentEnvelope['status_changed_at_date'] = $currentEnvelope['completed_at_date'];
                }

                break;
            case 'declined':
                if (EnvelopeStatuses::DECLINED !== $currentEnvelope['status']) {
                    $currentEnvelope['status'] = EnvelopeStatuses::DECLINED;
                    $currentEnvelope['declined_at_date'] = $currentEnvelope['declined_at_date'] ?? new DateTimeImmutable();
                    $currentEnvelope['status_changed_at_date'] = $currentEnvelope['declined_at_date'];
                }

                break;
            case 'delivered':
                if (EnvelopeStatuses::DELIVERED !== $currentEnvelope['status']) {
                    $currentEnvelope['status'] = EnvelopeStatuses::DELIVERED;
                    $currentEnvelope['status_changed_at_date'] = $date($digitalEnvelope->getDeliveredDateTime() ?: null) ?? new DateTimeImmutable();
                }

                break;
            case 'signed':
                if (EnvelopeStatuses::SIGNED !== $currentEnvelope['status']) {
                    $currentEnvelope['status'] = EnvelopeStatuses::SIGNED;
                    $currentEnvelope['status_changed_at_date'] = new DateTimeImmutable();
                }

                break;
            case 'voided':
                if (EnvelopeStatuses::VOIDED !== $currentEnvelope['status']) {
                    $currentEnvelope['status'] = EnvelopeStatuses::VOIDED;
                    $currentEnvelope['void_reason'] = $digitalEnvelope->getVoidedReason();
                    $currentEnvelope['voided_at_date'] = $currentEnvelope['voided_at_date'] ?? new DateTimeImmutable();
                    $currentEnvelope['deleted_at_date'] = $currentEnvelope['deleted_at_date'] ?? $currentEnvelope['voided_at_date'];
                    $currentEnvelope['status_changed_at_date'] = $currentEnvelope['voided_at_date'];

                    $historyPool->add([new HistoryEvent(HistoryEvent::VOID), $currentEnvelope['id_sender'], $currentEnvelope['voided_at_date']]);
                }

                break;
        }

        $currentRoutingOrder = (int) $digitalEnvelope->getRecipients()->getCurrentRoutingOrder();
        $currentWorkflowStep = $workflowSteps->last() ?: null;
        // Renew the routing order.
        $currentEnvelope['current_routing_order'] = $currentRoutingOrder;
        // If envelope is finished we need to finalize the workflow steps.
        if (in_array($currentEnvelope['status'], EnvelopeStatuses::FINISHED)) {
            if (EnvelopeWorkflowStatuses::COMPLETED !== $currentEnvelope['workflow_status']) {
                $currentEnvelope['workflow_status'] = EnvelopeWorkflowStatuses::COMPLETED;
                $currentEnvelope['workflow_completed_at'] = new DateTimeImmutable();
            }

            $workflowSteps->set($workflowSteps->indexOf($currentWorkflowStep), with($currentWorkflowStep, function (array $step) {
                if (WorkflowStepStatuses::COMPLETED !== $step['status']) {
                    $step['status'] = WorkflowStepStatuses::COMPLETED;
                    $step['completed_at_date'] = new DateTimeImmutable();
                }

                return $step;
            }));
        } else {
            $currentEnvelope['workflow_status'] = EnvelopeWorkflowStatuses::IN_PROGRESS;
        }

        return $currentEnvelope;
    }

    /**
     * Returns the recipients that must be synchronized.
     *
     * @param Collection<(CertifiedDelivery|Signer|ModelInterface)> $digitalRecipients
     */
    private function prepareRecipientsForSynchronization(
        array $currentEnvelope,
        array $originalRecipients,
        Collection $digitalRecipients,
        Collection $historyPool
    ): array {
        $date = fn (?string $dateString) => null === $dateString ? null : new DateTimeImmutable($dateString);

        // First of all let's take the list of current envelope recipients
        $recipientsList = [];
        $originalRecipients = arrayCollapse(
            array_map(
                fn (array $recipient) => [(string) $recipient['uuid'] => $recipient],
                iterator_to_array($currentEnvelope['recipients_routing']['recipients'] ?? new ArrayCollection())
            )
        );
        /** @var CertifiedDelivery|Signer $digitalRecipient */
        foreach ($digitalRecipients as $digitalRecipient) {
            if (null === ($recipient = $originalRecipients[$digitalRecipient->getRecipientId()] ?? null)) {
                throw new OutOfBoundsException(
                    sprintf(
                        'The digital recipient with ID "%s" is not found in the list of the recipients for envelope "%s".',
                        $digitalRecipient->getRecipientId(),
                        $currentEnvelope['id']
                    )
                );
            }

            // Just in case when the completed status is sent by DocuSign earlier that signed
            $isAlreadySigned = null !== $recipient['signed_at_date'];

            // Re-assign the dates in the recipient
            $recipient['sent_at_date'] = $recipient['sent_at_date'] ?? $date($digitalRecipient->getSentDateTime() ?: null);
            $recipient['delivery_at_date'] = $recipient['delivery_at_date'] ?? $date($digitalRecipient->getDeliveredDateTime() ?: null);
            $recipient['declined_at_date'] = $recipient['delivery_at_date'] ?? $date($digitalRecipient->getDeclinedDateTime() ?: null);
            if (RecipientTypes::SIGNER === $recipient['type']) {
                $recipient['signed_at_date'] = $recipient['signed_at_date'] ?? $date($digitalRecipient->getSignedDateTime() ?: null);
                $recipient['completed_at_date'] = $recipient['completed_at_date'] ?? $recipient['signed_at_date'];
                $recipient['confirmed_at_date'] = $recipient['confirmed_at_date'] ?? $recipient['completed_at_date'];
            } elseif (RecipientTypes::VIEWER === $recipient['type']) {
                $recipient['completed_at_date'] = $recipient['completed_at_date'] ?? $date($digitalRecipient->getSignedDateTime() ?: null);
            }

            // Save the original into separate value
            $originalRecipient = $recipient;
            // Based on the status, some recipient data will be updated.
            switch ($digitalRecipient->getStatus()) {
                case 'sent':
                    if (RecipientStatuses::SENT !== $recipient['status']) {
                        $recipient['status'] = RecipientStatuses::SENT;
                        $recipient['sent_at_date'] = $recipient['sent_at_date'] ?? new DateTimeImmutable();
                    }

                    break;
                case 'delivered':
                case 'faxpending':
                case 'autoresponded':
                    if (RecipientStatuses::DELIVERED !== $recipient['status']) {
                        $recipient['status'] = RecipientStatuses::DELIVERED;
                        $recipient['delivery_at_date'] = $recipient['delivery_at_date'] ?? new DateTimeImmutable();

                        if ('autoresponded' === $digitalRecipient->getStatus()) {
                            $historyPool->add([new HistoryEvent(HistoryEvent::AUTORESPONDED), $recipient['id_user'], $recipient['declined_at_date']]);
                        } else {
                            $historyPool->add([new HistoryEvent(HistoryEvent::DELIVER),  $recipient['id_user'], $recipient['delivery_at_date']]);
                        }
                    }

                    break;
                case 'completed':
                    if (RecipientStatuses::COMPLETED !== $recipient['status']) {
                        $recipient['status'] = RecipientStatuses::COMPLETED;
                        $recipient['completed_at_date'] = $recipient['completed_at_date'] ?? new DateTimeImmutable();

                        if (RecipientTypes::SIGNER === $recipient['type']) {
                            if (!$isAlreadySigned) {
                                $recipient['signed_at_date'] = $recipient['signed_at_date'] ?? new DateTimeImmutable();
                                $recipient['confirmed_at_date'] = $recipient['confirmed_at_date'] ?? new DateTimeImmutable();

                                $historyPool->add([new HistoryEvent(HistoryEvent::SIGN), $recipient['id_user'], $recipient['signed_at_date']]);
                            }
                        } elseif (RecipientTypes::VIEWER === $recipient['type']) {
                            $historyPool->add([new HistoryEvent(HistoryEvent::VIEW), $recipient['id_user'], $recipient['completed_at_date']]);
                        }
                    }

                    break;
                case 'declined':
                    if (RecipientStatuses::DECLINED !== $recipient['status']) {
                        $recipient['status'] = RecipientStatuses::DECLINED;
                        $recipient['declined_at_date'] = $recipient['declined_at_date'] ?? new DateTimeImmutable();
                        $recipient['decline_reason'] = $digitalRecipient->getDeclinedReason();

                        $historyPool->add([new HistoryEvent(HistoryEvent::DECLINE), $recipient['id_user'], $recipient['declined_at_date']]);
                    }

                    break;
                case 'signed':
                    if (RecipientStatuses::SIGNED !== $recipient['status']) {
                        $recipient['status'] = RecipientStatuses::SIGNED;
                        $recipient['signed_at_date'] = $recipient['signed_at_date'] ?? new DateTimeImmutable();
                        $recipient['confirmed_at_date'] = $recipient['confirmed_at_date'] ?? new DateTimeImmutable();

                        $historyPool->add([new HistoryEvent(HistoryEvent::SIGN),  $recipient['id_user'], $recipient['signed_at_date']]);
                    }

                    break;
                case 'created':
                    // We are  not interested in these stases.
                    // So we skip them.
                    break;
            }

            // After update we need to compare the original recipients values and
            // the updated ones, IF they exist
            if ($recipient !== $originalRecipient) {
                $recipientsList[] = $recipient;
            }
        }

        return arrayCollapse(
            array_map(
                fn (array $recipient) => [(string) $recipient['uuid'] => $recipient],
                $recipientsList
            )
        );
    }

    /**
     * Returns the workflow steps that must be synchronized.
     *
     * @param Collection<(CertifiedDelivery|Signer|ModelInterface)> $digitalRecipients
     */
    private function prepareWorkflowStepsForSynchronization(array $currentEnvelope, Collection $digitalRecipients, int $digitalRoutingOrder): Collection
    {
        /** @var Collection $workflowSteps */
        $workflowSteps = isset($currentEnvelope['workflow']['steps']) ? clone $currentEnvelope['workflow']['steps'] : new ArrayCollection();
        $currentRoutingOrder = $currentEnvelope['current_routing_order'];
        $currentWorkflowStep = $workflowSteps->last() ?: null;
        if ($currentRoutingOrder < $digitalRoutingOrder) {
            if (null !== $currentWorkflowStep) {
                $workflowSteps->set($workflowSteps->indexOf($currentWorkflowStep), with($currentWorkflowStep, function (array $step) {
                    $step['status'] = WorkflowStepStatuses::COMPLETED;
                    $step['completed_at_date'] = new DateTimeImmutable();

                    return $step;
                }));
            }

            while ($currentRoutingOrder !== $digitalRoutingOrder) {
                ++$currentRoutingOrder;
                $recipientsForStep = $digitalRecipients->filter(fn ($recipient) => $recipient->getRoutingOrder() === $currentRoutingOrder)->count();
                $workflowStep = [
                    'id_envelope' => $currentEnvelope['id'],
                    'uuid'        => Uuid::uuid6(),
                    'status'      => WorkflowStepStatuses::IN_PROGRESS,
                    'action'      => $recipientsForStep > 1 ? WorkflowStepTypes::PARALLEL_RECIPIENT_ROUTING : WorkflowStepTypes::RECIPIENT_ROUTING,
                    'step_order'  => $currentRoutingOrder,
                ];
                if ($currentRoutingOrder !== $digitalRoutingOrder) {
                    $workflowStep['status'] = WorkflowStepStatuses::COMPLETED;
                    $workflowStep['completed_at_date'] = new DateTimeImmutable();
                }

                $workflowSteps->add($workflowStep);
            }
        }

        return $workflowSteps;
    }

    /**
     * Returns the document reader for synchronized envelope.
     */
    private function preprareDocumentReaderForSynchronization(
        ?string $newStatus,
        array $recipientsForUpdate,
        DocuSignModels\Envelope $digitalEnvelope,
        object $decodedRequestBody
    ): ?Closure {
        if (
            !in_array($newStatus, [...EnvelopeStatuses::FINISHED, EnvelopeStatuses::SIGNED])
            && empty(
                array_filter(
                    $recipientsForUpdate,
                    fn (array $recipient) => in_array($recipient['status'], [...RecipientStatuses::FINALIZED, RecipientStatuses::SIGNED]),
                )
            )
        ) {
            return null;
        }

        $exportedDocuments = array_filter($decodedRequestBody->envelopeDocuments ?? [], fn (object $d) => 'content' === $d->type || 'summary' === $d->type);
        $fallbackReader = function () use ($digitalEnvelope) {
            $fileStream = fopen('php://memory', 'rw+');
            $temporaryFile = $this->getDocusignProvider()->getDocument($digitalEnvelope->getEnvelopeId(), 'combined', null, [
                'watermark'   => false,
                'certificate' => true,
            ]);
            fwrite($fileStream, $temporaryFile->fread($temporaryFile->getSize()));
            rewind($fileStream);

            return $fileStream;
        };
        if (empty($exportedDocuments)) {
            return $fallbackReader;
        }

        /** @var \Mpdf\Mpdf $mpdf */
        $mpdf = library(TinyMVC_Library_mpdf::class)->formatFreePdf(['dpi' => 300]);
        $mpdf->SetTitle('Combined Documents');
        $mpdf->SetAuthor('ExportPortal via DocuSign');
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempPrefixer = $storageProvider->prefixer('temp.storage');

        // Lambda that allows to read the combined document
        return function () use ($mpdf, $tempDisk, $exportedDocuments, $fallbackReader, $digitalEnvelope, $tempPrefixer) {
            try {
                $fileNumber = 1;
                foreach ($exportedDocuments as $document) {
                    $fileName = sprintf('%s.%s.pdf', uniqid('file.', true), $document->type);
                    $filePath = "/docusign/{$fileName}";
                    $fullPath = $tempPrefixer->prefixPath($filePath);
                    $tempDisk->write($filePath, base64_decode($document->PDFBytes));
                    $pagesInFile = $mpdf->setSourceFile($fullPath);
                    for ($i = 1; $i <= $pagesInFile; ++$i) {
                        $templateId = $mpdf->importPage($i);
                        $templateSizes = $mpdf->getTemplateSize($templateId);
                        $mpdf->AddPageByArray([
                            'orientation' => $templateSizes['orientation'],
                            'sheet-size'  => $templateSizes['width'] < $templateSizes['height']
                                ? [$templateSizes['width'], $templateSizes['height']]
                                : [$templateSizes['height'], $templateSizes['width']],
                        ]);
                        $mpdf->useTemplate($templateId);
                    }
                    ++$fileNumber;
                    $tempDisk->delete($filePath);
                }

                $fileStream = fopen('php://memory', 'rw+');
                fwrite($fileStream, $mpdf->Output('combined.pdf', \Mpdf\Output\Destination::STRING_RETURN));
                rewind($fileStream);

                return $fileStream;
            } catch (\Throwable $e) {
                $this->getLogger()->error(
                    sprintf('Failed to combine the documents for envelope "%s" due to error: %s', $digitalEnvelope->getEnvelopeId(), $e->getMessage()),
                    [
                        'envelopeId' => $digitalEnvelope->getEnvelopeId(),
                        'exception'  => $e,
                        'previous'   => $e->getPrevious(),
                    ]
                );

                return $fallbackReader();
            }
        };
    }

    /**
     * Writes the envelope sync event.
     */
    private function writeEnvelopeHistory(int $envelopeId, Collection $historyPool): void
    {
        /** @var Envelope_History_Model $historyRepository */
        $historyRepository = model(Envelope_History_Model::class);
        $pureList = $historyPool->toArray();
        usort($pureList, fn (array $prev, array $next) => $prev[2] <=> $next[2]);

        $writePool = [];
        foreach ($pureList as list($event, $userId, $eventDate, $isInternal)) {
            $writePool[] = [
                'id_user'         => $userId ?? null,
                'event'           => $event,
                'context'         => ['isInternal' => $isInternal ?? false, 'useWebhook' => true],
                'id_envelope'     => $envelopeId,
                'created_at_date' => $eventDate,
                'updated_at_date' => $eventDate,
            ];
        }

        // Write history.
        $historyRepository->runUnguarded(function () use ($writePool, $envelopeId, $historyRepository) {
            if (!empty($writePool)) {
                $historyRepository->insertMany($writePool);
            }
            $historyRepository->insertOne([
                'id_user'         => null,
                'event'           => new HistoryEvent(HistoryEvent::SYNC),
                'context'         => ['isInternal' => true, 'useWebhook' => true],
                'id_envelope'     => $envelopeId,
                'created_at_date' => new DateTimeImmutable(),
                'updated_at_date' => new DateTimeImmutable(),
            ]);
        });
    }

    /**
     * Sends the notifications.
     */
    private function sendEnvelopeNotifications(
        Notifier $notifier,
        array $currentEnvelope,
        Collection $allRecipients,
        ?array $updatedEnvelope,
        array $updatedRecipients
    ): void {
        if (null === $updatedEnvelope && empty($updatedRecipients)) {
            return;
        }

        $sender = $currentEnvelope['sender'] ?? [];
        $senderId = (int) ($sender['id'] ?? $currentEnvelope['id_sender']);
        $orderId = $currentEnvelope['order_reference']['id_order'];
        $envelopeId = $currentEnvelope['id'];
        $allSigners = $allRecipients->filter(fn (array $recipient) => RecipientTypes::SIGNER === $recipient['type']);
        $allViewers = $allRecipients->filter(fn (array $recipient) => RecipientTypes::VIEWER === $recipient['type']);
        $mobRecipient = $allRecipients->map(fn (array $recipient) => (new Recipient((int) $recipient['id_user']))->withRoomType(RoomType::CARGO()))->toArray();
        $senderRecipient = (new Recipient($senderId))->withRoomType(RoomType::CARGO());
        $combinedRecipients = [
            $senderRecipient,
            ...array_filter($mobRecipient, fn (Recipient $recipient) => $recipient->getUserId() !== $senderId),
        ];
        $notificationsPull = new ArrayCollection();
        $publicChannels = [(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()];
        $privateChannels = [(string) SystemChannel::STORAGE()];

        // Wal over updated recipients
        foreach ($updatedRecipients as $recipient) {
            $notificationSender = new Sender(
                $recipient['id_user'],
                $recipient['full_name'],
                $recipient['legal_name'],
                $recipient['group_type']
            );

            switch ($recipient['status']) {
                case RecipientStatuses::SIGNED:
                case RecipientStatuses::COMPLETED:
                    if (RecipientTypes::SIGNER === $recipient['type']) {
                        $notificationsPull->add([
                            new OrderDocumentEnvelopeNotification(Type::SIGNED_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $publicChannels),
                            $senderRecipient,
                        ]);
                        $notificationsPull->add([
                            new OrderDocumentEnvelopeNotification(Type::SIGNED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $privateChannels),
                            new RightfulRecipient(['monitor_documents']),
                        ]);
                    } elseif (RecipientTypes::VIEWER === $recipient['type']) {
                        $notificationsPull->add([
                            new OrderDocumentEnvelopeNotification(Type::VIEWED_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $publicChannels),
                            $senderRecipient,
                        ]);
                        $notificationsPull->add([
                            new OrderDocumentEnvelopeNotification(Type::VIEWED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $privateChannels),
                            new RightfulRecipient(['monitor_documents']),
                        ]);
                    }

                    break;
                case RecipientStatuses::DECLINED:
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::DECLINED_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $publicChannels),
                        $senderRecipient,
                    ]);
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::DECLINED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $privateChannels),
                        new RightfulRecipient(['monitor_documents']),
                    ]);

                    break;

                default:
                    break;
            }
        }

        // Giving the current (new) status of the envelope, we need to prepare notifications,
        // that must be sent from sender to recipients.
        if (null !== $updatedEnvelope && isset($updatedEnvelope['status'])) {
            $notificationSender = new Sender(
                $sender['id'],
                $sender['full_name'],
                $sender['legal_name'],
                $sender['group_type']
            );

            switch ($updatedEnvelope['status']) {
                case EnvelopeStatuses::SENT:
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::SENT_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $publicChannels),
                        $mobRecipient,
                    ]);
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::SENT_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $privateChannels),
                        new RightfulRecipient(['monitor_documents']),
                    ]);

                    break;
                case EnvelopeStatuses::VOIDED:
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::VOIDED_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $publicChannels),
                        $mobRecipient,
                    ]);
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::VOIDED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], $notificationSender, $privateChannels),
                        new RightfulRecipient(['monitor_documents']),
                    ]);

                    break;
                case EnvelopeStatuses::COMPLETED:
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::COMPLETED_ENVELOPE, $orderId, $envelopeId, $currentEnvelope['display_title'], null, $publicChannels),
                        $combinedRecipients,
                    ]);
                    $notificationsPull->add([
                        new OrderDocumentEnvelopeNotification(Type::COMPLETED_ENVELOPE_FOR_MANAGER, $orderId, $envelopeId, $currentEnvelope['display_title'], null, $privateChannels),
                        new RightfulRecipient(['monitor_documents']),
                    ]);

                    break;

                default:
                    // Nothing here, go away
                    break;
            }
        }

        // Walk notifications and send them
        foreach ($notificationsPull as $entry) {
            $notifier->send(array_shift($entry), ...(array) ($entry ?? []));
        }
    }

    /**
     * Returns the EPDocs API client.
     */
    private function getDocumentsApiClient(): RestClient
    {
        $client = new Client(['base_uri' => config('env.EP_DOCS_HOST', 'http://localhost')]);
        $configs = (new \App\Plugins\EPDocs\Configuration())
            ->setHttpOrigin(config('env.EP_DOCS_REFERRER'))
            ->setDefaultUserId(config('env.EP_DOCS_ADMIN_SALT'))
        ;
        $auth = new Auth($client, new Bearer(), new JwtTokenStorage(
            $client,
            new JwtCredentials(
                config('env.EP_DOCS_API_USERNAME'),
                config('env.EP_DOCS_API_SECRET')
            )
        ));

        return new RestClient($client, $auth, $configs);
    }

    /**
     * Falttens the recipients container into the of recipients.
     *
     * @return Collection<(Agent|CarbonCopy|CertifiedDelivery|Editor|InPersonSigner|Intermediary|Notary|Signer|Witness)>
     */
    private function flattenRecipients(DocuSignModels\Recipients $envelopeRecipients, bool $onlySupported = false): Collection
    {
        $allRecipientsList = new ArrayCollection([
            ...$envelopeRecipients->getSigners() ?? [],
            ...$envelopeRecipients->getAgents() ?? [],
            ...$envelopeRecipients->getEditors() ?? [],
            ...$envelopeRecipients->getNotaries() ?? [],
            ...$envelopeRecipients->getWitnesses() ?? [],
            ...$envelopeRecipients->getCarbonCopies() ?? [],
            ...$envelopeRecipients->getCertifiedDeliveries() ?? [],
            ...$envelopeRecipients->getInPersonSigners() ?? [],
            ...$envelopeRecipients->getIntermediaries() ?? [],
        ]);
        if (!$onlySupported) {
            return $allRecipientsList;
        }

        // Filter supported types
        return $allRecipientsList->filter(fn ($recipient) => in_array(get_class($recipient), $this->supportedRecipientTypes));
    }

    /**
     * Returns the difference between two lists.
     *
     * @param arrya $includeFields
     */
    private function getListDiff(array $original, array $modified, array $includeFields = []): array
    {
        $export = [];
        foreach ($original as $key => $value) {
            if (in_array($key, $includeFields) || !array_key_exists($key, $modified) || $value !== $modified[$key]) {
                $export[$key] = $value;
            }
        }

        return $export;
    }
}

// End of file docusign.php
// Location: /tinymvc/myapp/controllers/docusign.php
