<?php

declare(strict_types=1);

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Encryption\MasterKeyAwareTrait;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\File\Bridge\EpDocs\FileStorage;
use App\Common\File\Bridge\FileInterface;
use App\Common\File\Bridge\ReferenceInterface;
use App\Common\Http\SignsRequestsTrait;
use App\Common\Traits\DocumentsApiAwareTrait;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\ValidationException;
use App\Messenger\Message\Command\CreateDirectMatrixChatRoomNow;
use App\Messenger\Message\Event\MatrixUserKeysCreatedEvent;
use App\Plugins\EPDocs;
use App\Plugins\EPDocs\Http\Authentication\Bearer;
use App\Services\ChatService;
use App\Validators\DirectChatRoomValidator;
use App\Validators\EpDocsTemporaryFilesValidator;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use ParagonIE\Halite\Asymmetric\Crypto;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Controller Matrix_Chat_Controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Matrix_Chat_Controller extends TinyMVC_Controller
{
    use MasterKeyAwareTrait;
    use SignsRequestsTrait;
    use DocumentsApiAwareTrait;
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    private const SIGNED_URL_CIPHERTEXT_SESSION_KEY = 'appSessionUrlSigningKey';
    private const SIGNED_URL_MAC_QUERY_KEY = 'verificationCode';
    private const SIGNED_URL_STATE_KEY = 'operationState';
    private const SIGNED_URL_TTL = 3600;

    /**
     * Executes actions on controller by AJAX.
     */
    public function ajax_operation(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_messages');
        if (userStatus()->isLimited()) {
            jsonResponse(translate('systmess_error_permission_not_granted', null, true));
        }

        $request = request();
        /** @var int $userId */
        $userId = (int) privileged_user_id();

        try {
            switch (uri()->segment(3)) {
                case 'find-users':
                    if ('active' !== user_status()) {
                        jsonResponse(translate('systmess_error_permission_not_granted'));
                    }
                    $this->findUsers(
                        $this->get(MatrixConnector::class),
                        $userId,
                        $request->get('users_module'),
                        $request->get('keywords'),
                        (int) $request->get('page'),
                        (int) $request->get('limit')
                    );

                    break;
                case 'create-direct-chat-room':
                    try {
                        $this->createDirectChatRoom($request, model(User_Model::class), $userId, (int) uri()->segment(4) ?: null);
                    } catch (AccessDeniedException $e) {
                        jsonResponse(
                            throwableToMessage($e, translate('systmess_error_cannot_contact_yourself', null, true)),
                            'error',
                            !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
                        );
                    }

                    break;
                case 'get-security-passphrase':
                    $this->getSecurityPhraseInfo(container()->get(MatrixConnector::class), $userId);

                    break;
                case 'update-keys-statuses':
                    $this->updateKeysStatus(container()->get(MatrixConnector::class), \model(Matrix_Users_Model::class), $userId);

                    break;
                case 'move-attachments':
                    $this->moveAttachments($request, $userId, have_right('manage_content'));

                    break;
                case 'access-attachment':
                    $this->getFileAccessToken($userId, uri()->segment(5), have_right('manage_content'));

                    break;

                default:
                    json(['message' => translate('systmess_error_route_not_found', null, true), 'mess_type' => 'error'], 404);

                    break;
            }
        } catch (NotFoundException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_invalid_data', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (AccessDeniedException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_merge(
                    \array_map(
                        fn (ConstraintViolation $violation) => $violation->getMessage(),
                        \iterator_to_array($e->getValidationErrors()->getIterator())
                    ),
                ),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
    }

    /**
     * Shows the page where E2E keys generatation can begin.
     */
    public function keygen_start(): void
    {
        $request = request();

        // Get the user from query ID value.
        /** @var MatrixConnector $matrixConnector */
        $matrixConnector = $this->get(MatrixConnector::class);
        $userId = $request->query->getInt('userId') ?: null;
        if (
            null === $userId
            || empty($userReference = $matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId))
        ) {
            redirect('/404');
        }

        // Next, let's validate the request signature.
        $isValidSignature = false;
        $macKey = $request->query->get('verificationCode') ?: null;

        try {
            if (null !== $macKey) {
                $isValidSignature = Crypto::verify($userReference['username'], $this->getMasterKey()->getPublicKey(), (string) $macKey);
            }
        } catch (\Throwable $e) {
            // If we failed for some reason, then the signature is invalid.
            $isValidSignature = false;
        }
        if (!$isValidSignature) {
            redirect('/403');
        }

        views(
            ['new/header_view', 'new/matrix_chat/initialization/index_view'],
            $userReference['has_initialized_keys'] ? [
                'initialized' => true,
            ] : [
                'userId'      => $userId,
                'initialized' => false,
                'credentials' => [
                    'profileId'  => $userReference['profile_room_id'],
                    'matrixId'   => $userReference['mxid'],
                    'username'   => $userReference['username'],
                    'password'   => $userReference['password'],
                    'hasKeys'    => $userReference['has_initialized_keys'],
                    'passphrase' => $userReference['security_phrase'],
                ],
                'redirectUrl' => $this->signRedirectResponse(
                    $request,
                    new RedirectResponse(getUrlForGroup("/matrix_chat/keygen_finalize?userId={$userId}")),
                    static::SIGNED_URL_TTL
                )->getTargetUrl(),
            ]
        );
    }

    /**
     * Final part of the matrix E2E keys generatation.
     */
    public function keygen_finalize(): void
    {
        $request = request();
        /** @var MatrixConnector $matrixConnector */
        $matrixConnector = $this->get(MatrixConnector::class);
        /** @var Matrix_Users_Model $matrixReferencesRepository */
        $matrixReferencesRepository = model(Matrix_Users_Model::class);

        try {
            $this->verifySignedRequest($request);
            $this->updateKeysStatus($matrixConnector, $matrixReferencesRepository, $request->query->getInt('userId'));
        } catch (NotFoundException $e) {
            $errorMessage = throwableToMessage($e, translate('systmess_error_invalid_data', null, true));
            $errorCode = 404;
        } catch (AccessDeniedException $e) {
            $errorMessage = throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true));
            $errorCode = 403;
        } catch (\Throwable $e) {
            $errorMessage = throwableToMessage($e, translate('systmess_error_invalid_data', null, true));
            $errorCode = 500;
        } finally {
            if (isset($e)) {
                json(['message' => $errorMessage], $e->getCode() ?: $errorCode);
            }
        }

        json(null, 200);
    }

    /**
     * Finds the users for new room popup.
     */
    protected function findUsers(MatrixConnector $matrixConnector, int $userId, ?string $module, ?string $keywords, int $page, int $perPage): void
    {
        $safeModule = cleanInput($module) ?: null;
        $safeKeywords = trim($keywords) ?: null;
        if (empty($safeModule) && empty($safeKeywords)) {
            jsonResponse(null, 'success', ['data' => [], 'total' => 0, 'hasMorePages' => false]);
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);

        $users = $elasticsearchUsersModel->getUsers([
            'notId'             => $userId,
            'nameOrCompanyName' => $safeKeywords,
            'perPage'           => 1000, //artificial restriction of search results
        ]);

        if (empty($users)) {
            jsonResponse(null, 'success', ['data' => [], 'total' => 0, 'hasMorePages' => false]);
        }

        /** @var Users_Model $usersRepository */
        $usersRepository = model(Users_Model::class);

        $relationBuilder = $usersRepository->getRelationsRuleBuilder();

        $existsFilters = [];
        $existsFilters[] = $relationBuilder->has('matrixReference');
        $scopes['ids'] = array_column($users, 'id');

        switch ($safeModule) {
            case 'folowers':
                $existsFilters[] = $relationBuilder->whereHas('followings', function (QueryBuilder $query) use ($userId) {
                    $query->andWhere("id_user = {$query->createNamedParameter($userId, null, ':userId')}");
                });

                break;
            case 'contacts':
                /** @var User_Contacts_Model $userContacts */
                $userContacts = \model(User_Contacts_Model::class);
                $contactsIds = \array_unique(
                    \array_column($userContacts->findAllBy(['scopes' => ['user' => $userId]]), 'id_contact_user')
                );

                // If the amount of contact users is 0 then we can return empty list
                if (empty($contactsIds) || empty($scopes['ids'] = array_intersect($scopes['ids'], $contactsIds))) {
                    jsonResponse(null, 'success', ['data' => [], 'total' => 0, 'hasMorePages' => false]);
                }

                break;
            case 'saved_sellers':
                $existsFilters[] = $relationBuilder->whereHas('sellerCompany', function (QueryBuilder $query, RelationInterface $relation) use ($userId, $usersRepository) {
                    $parentTable = $relation->getRelated()->getTable();
                    $childTable = $usersRepository->getRelation('savedSellers')->getRelated()->getTable();
                    $childTableAlias = sprintf('%s_nested', $childTable);
                    $query
                        ->leftJoin($parentTable, $childTable, $childTableAlias, "{$parentTable}.id_company = {$childTableAlias}.company_id")
                        ->andWhere("{$childTableAlias}.user_id = {$query->createNamedParameter($userId, null, ':userId')}")
                    ;
                });

                break;
            case 'b2b':
                have_right('have_company');
                $existsFilters[] = $relationBuilder->whereHas('anySellerCompany.partners', function (QueryBuilder $query) {
                    $query->andWhere("id_partner = {$query->createNamedParameter(my_company_id(), null, ':partnerId')}");
                });

                break;
            case 'buyers':
                $existsFilters[] = $relationBuilder->whereHas('buyerProductOrders', function (QueryBuilder $query) use ($userId) {
                    $query->andWhere("id_seller = {$query->createNamedParameter($userId, null, ':userId')}");
                });

                break;

            default:
                // if search is empty, then we can leave with empty list
                if (empty($safeKeywords)) {
                    jsonResponse(null, 'success', ['data' => [], 'total' => 0, 'hasMorePages' => false]);
                }

                break;
        }

        $idsList = implode(',', $scopes['ids']);

        // Get the paginator for the users.
        $paginator = $usersRepository->paginate(
            [
                'columns' => ['*', 'TRIM(CONCAT(fname, " ", lname)) as `full_name`'],
                'with'    => [
                    'group',
                    'sellerCompany as seller_company'     => function (RelationInterface $relation) {
                        $relation->getQuery()->select($relation->getExistenceCompareKey(), 'name_company AS `name`', 'legal_name_company AS `legal_name`');
                    },
                    'shipperCompany as shipper_company'   => function (RelationInterface $relation) {
                        $relation->getQuery()->select($relation->getExistenceCompareKey(), 'co_name AS `name`', 'legal_co_name AS `legal_name`');
                    },
                    'matrixReference as matrix_reference' => function (RelationInterface $relation) use ($matrixConnector) {
                        $relation->getRelated()->getScope('version')(
                            $relation->getQuery(),
                            $matrixConnector->getConfig()->getSyncVersion()
                        );
                    },
                ],
                'exists'  => $existsFilters,
                'scopes'  => $scopes,
                'order'   => ["FIELD(`{$usersRepository->getTable()}`.`idu`, {$idsList})" => "ASC"], //preserving the order of the results obtained from elasticsearch
            ],
            $perPage,
            $page
        );
        // Transform the output into accepted format.
        $users = [];
        foreach ($paginator['data'] ?? [] as $user) {
            $users[] = [
                'id'         => $user['idu'],
                'online'     => $user['logged'],
                'avatar'     => getDisplayImageLink(['{ID}' => $user['idu'], '{FILE_NAME}' => $user['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $user['user_group']]),
                'name'       => $user['full_name'],
                'group'      => $user['is_verified'] ? $user['group']['gr_name'] : \trim(\str_replace('Verified', '', $user['group']['gr_name'])),
                'company'    => decodeCleanInput((string) ($user['seller_company']['name'] ?? $user['shipper_company']['name'] ?? null)),
                'mxId'       => $user['matrix_reference']['mxid'] ?? null,
                'mxUserName' => $user['matrix_reference']['username'] ?? null,
            ];
        }

        jsonResponse(null, 'success', [
            'data'         => $users,
            'total'        => $paginator['total'],
            'hasMorePages' => $paginator['has_more_pages'],
        ]);
    }

    /**
     * Creates direct chat room.
     *
     * @param Request    $request         the HTTP request
     * @param User_Model $usersRepository the users repository
     * @param int        $userId          the current user ID
     * @param null|int   $recipientId     the recipient user ID
     */
    protected function createDirectChatRoom(Request $request, User_Model $usersRepository, int $userId, ?int $recipientId): void
    {
        //region Access check
        if (null === $recipientId || empty($recipient = $usersRepository->get_user_by_condition(['id_user' => $recipientId]))) {
            throw new NotFoundException('The recipient for direct messages is required.');
        }
        if ($userId === $recipientId) {
            throw new OwnershipException('You cannot contact yourself.');
        }
        if ('active' !== $recipient['status']) {
            throw new AccessDeniedException('You cannot contact not active users.');
        }
        //endregion Access check

        //region Validate
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new DirectChatRoomValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Prepare message
        $chatService = new ChatService();
        $subject = $request->request->get('subject');
        $resourceId = $request->request->getInt('resource_id') ?: null;
        $resourceType = ResourceType::from($request->request->get('resource_type'));
        if (null === $subject) {
            $subject = $chatService->makeSubjectForType($resourceType, $userId, $recipientId, $resourceId);
        }
        $roomMessage = new CreateDirectMatrixChatRoomNow(
            $subject,
            $userId,
            $recipientId,
            (new ResourceOptions())->type($resourceType)->id((string) $resourceId ?: null)
        );
        //endregion Prepare message

        //region Create room
        try {
            /** @var MessengerInterface $messenger */
            $messenger = container()->get(MessengerInterface::class);
            $messenger->bus('command.bus')->dispatch($roomMessage);
        } catch (\Throwable $e) {
            jsonResponse(
                throwableToMessage($e, 'Failed to send message to the user.'),
                // throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        $room = $chatService->getRoomForType($resourceType, $userId, $recipientId, $resourceId);
        //endregion Create room

        jsonResponse(null, 'success', ['room' => $room['room_id']]);
    }

    /**
     * Returns the information for security passphrase.
     */
    protected function getSecurityPhraseInfo(MatrixConnector $matrixConnector, int $userId): void
    {
        if (
            null === $userId
            || empty($userReference = $matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId))
        ) {
            throw new NotFoundException('The user matrix reference is not found');
        }

        $allowedStatuses = [UserStatus::from(UserStatus::FRESH), UserStatus::from(UserStatus::PENDING), UserStatus::from(UserStatus::ACTIVE)];
        if (!in_array($userReference['user']['status'] ?? null, $allowedStatuses)) {
            throw new AccessDeniedException('You cannot get the security phrase for users with invalid statuses.');
        }

        jsonResponse(null, 'success', ['passPhrase' => $userReference['security_phrase']]);
    }

    /**
     * Updates key status.
     */
    protected function updateKeysStatus(MatrixConnector $matrixConnector, Matrix_Users_Model $matrixReferencesRepository, ?int $userId): void
    {
        if (
            null === $userId
            || empty($userReference = $matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId))
        ) {
            throw new NotFoundException('The user matrix reference is not found');
        }

        if (!$userReference['has_initialized_keys']) {
            $matrixReferencesRepository->updateOne($userReference['id'], ['has_initialized_keys' => true]);
            // Send notification to the bus informing that the user's E2E keys were created.
            $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new MatrixUserKeysCreatedEvent($userId));
        }

        jsonResponse(null, 'success');
    }

    /**
     * Moves the attachments from temporary registry to the real one.
     */
    protected function moveAttachments(Request $request, int $userId, bool $isAdministration = false): void
    {
        //region Validation
        $rules = config('files.messages.attach');
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new EpDocsTemporaryFilesValidator($adapter, 1, $rules['limit'] ?? 1, null, ['files' => 'Attachments'], ['files' => 'attachments']);
        if (!$validator->validate(request()->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Write files
        try {
            $cachePool = library(TinyMVC_Library_Fastcache::class)->pool('epdocs');
            $fileStorage = new FileStorage($this->getApiClient(true), config('env.EP_DOCS_REFERRER', 'http://localhost'), config('env.EP_DOCS_ADMIN_SALT'), $cachePool);
            /** @var iterable<UuidInterface, FileInterface> */
            $newFiles = $fileStorage->writeTemporaryFiles(
                $fileStorage->getTemporaryFiles(new ArrayIterator(array_map(
                    fn (string $fileId) => Uuid::fromString((string) base64_decode($fileId)),
                    (array) $request->request->get('attachments') ?? []
                ))),
                $userId,
                []
            );
        } catch (\Exception $exception) {
            jsonResponse(
                throwableToMessage(
                    $exception,
                    $isAdministration ? translate('systmess_document_failed_to_upload_temp_admin_error_message') : translate('systmess_upload_document_failed_message')
                ),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }
        //endregion Write files

        //region Prepare output
        $files = [];
        foreach ($newFiles as $file) {
            $files[] = [
                'name'         => $file->getName(),
                'owner'        => $userId,
                'fileId'       => $file->getUuid(),
                'originalName' => $file->getOriginalName(),
                'extension'    => $file->getExtension(),
                'type'         => $file->getType(),
                'size'         => $file->getSize(),
            ];
        }
        //endregion Prepare output

        jsonResponse(null, 'success', ['files' => $files]);
    }

    /**
     * Returns the EPDocs file access token.s.
     */
    protected function getFileAccessToken(int $userId, string $rawFileId, bool $isAdministration = false): void
    {
        //region Get Access token
        try {
            $tokens = [];
            $cachePool = library(TinyMVC_Library_Fastcache::class)->pool('epdocs');
            $fileStorage = new FileStorage($this->getApiClient(true), config('env.EP_DOCS_REFERRER', 'http://localhost'), config('env.EP_DOCS_ADMIN_SALT'), $cachePool);
            /**
             * @var FileInterface      $file
             * @var ReferenceInterface $accessToken
             */
            foreach ($fileStorage->getFilesAccessTokens(new ArrayIterator([Uuid::fromString($rawFileId)]), $userId, 300) as $file => $accessToken) {
                $tokens[] = [
                    'url'      => config('env.EP_DOCS_HOST', 'http://localhost') . $accessToken->getPath(),
                    'name'     => "{$file->getName()}.{$file->getExtension()}",
                    'filename' => $file->getOriginalName() ?? (($file->getName() ?? 'attachment') . $file->getExtension()),
                ];
            }
        } catch (AccessDeniedException $exception) {
            // Roll this exception forward.
            throw $exception;
        } catch (\Exception $exception) {
            jsonResponse(
                throwableToMessage(
                    $exception,
                    $isAdministration ? translate('systmess_document_download_admin_error_message') : translate('systmess_download_document_failed_message')
                ),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }
        //endregion Get Access token

        jsonResponse(null, 'success', ['token' => current($tokens)]);
    }

    /**
     * Returns the EPDOcs API client.
     */
    private function getFilesApi(): FileStorage
    {
        return new FileStorage(
            new EPDocs\Rest\RestClient(
                $httpClient = new Client(['base_uri' => config('env.EP_DOCS_HOST', 'http://localhost')]),
                new EPDocs\Http\Auth($httpClient, new Bearer(), new EPDocs\Storage\JwtTokenStorage(
                    $httpClient,
                    new EPDocs\Credentials\JwtCredentials(config('env.EP_DOCS_API_USERNAME'), config('env.EP_DOCS_API_SECRET'))
                )),
                (new EPDocs\Configuration())->setHttpOrigin(config('env.EP_DOCS_REFERRER'))->setDefaultUserId(config('env.EP_DOCS_ADMIN_SALT'))
            ),
            config('env.EP_DOCS_REFERRER', 'http://localhost'),
            config('env.EP_DOCS_ADMIN_SALT'),
            library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
        );
    }
}

// End of file matrix_chat.php
// Location: /tinymvc/myapp/controllers/matrix_chat.php
