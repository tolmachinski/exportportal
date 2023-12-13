<?php

declare(strict_types=1);

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\ProcessingException;
use App\Common\Exceptions\UserNotFoundException;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\DataProvider\ProfileEditRequestProvider;
use App\Renderer\ProfileEditRequestDatatableRenderer;
use App\Renderer\ProfileEditRequestViewRenderer;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use App\Services\EditRequest\ProfileEditRequestProcessingService;
use App\Validators\AddressValidator;
use App\Validators\EditRequestDocumentsValidator;
use App\Validators\LegalNameValidator;
use App\Validators\PhoneValidator;
use App\Validators\ReasonValidator;
use App\Validators\UserNameValidator;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\ReferencedRecipient;
use ExportPortal\Bridge\Notifier\Recipient\RightfulRecipient;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use function Symfony\Component\String\u;

/**
 * Controller Profile_Edit_Requests_Controller.
 */
class Profile_Edit_Requests_Controller extends TinyMVC_Controller
{
    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    /**
     * The profile edit request provider instance.
     */
    private ProfileEditRequestProvider $requestProvider;

    /**
     * The view renderer for profile edit requests,.
     */
    private ProfileEditRequestViewRenderer $requestRenderer;

    /**
     * The processing service for profile edit requests.
     */
    private ProfileEditRequestProcessingService $requestProcessingService;

    /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notifier = $container->get(NotifierInterface::class);
        $this->requestProvider = $container->get(ProfileEditRequestProvider::class);
        $this->requestRenderer = $container->get(ProfileEditRequestViewRenderer::class);
        $this->requestProcessingService = $container->get(ProfileEditRequestProcessingService::class);
    }

    /**
     * Index page.
     */
    public function index(): Response
    {
        return new RedirectResponse(getUrlForGroup('/profile_edit_requests/administration'));
    }

    /**
     * Shows administration page for profile edit requests.
     */
    public function administration(): void
    {
        checkAdmin('users_administration');
        views(['admin/header_view', 'admin/profile_edit_requests/index_view', 'admin/footer_view'], [
            'title'    => 'Profile edit requests',
            'filters'  => [
                'request' => with(
                    request()->query->getInt('request') ?: null,
                    fn (?int $id) => ['value' => $id, 'text' => orderNumber($id ?? 0)],
                ),
            ],
            'statuses' => [
                [EditRequestStatus::PENDING(), EditRequestStatus::PENDING()->label()],
                [EditRequestStatus::ACCEPTED(), EditRequestStatus::ACCEPTED()->label()],
                [EditRequestStatus::DECLINED(), EditRequestStatus::DECLINED()->label()],
            ],
        ]);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('users_administration');

        try {
            switch (uri()->segment(3)) {
                case 'details':
                    $this->showDetailsPopup((int) uri()->segment(4) ?: null);

                    break;
                case 'decline':
                    $this->showDeclineFormPopup((int) uri()->segment(4) ?: null);

                    break;

                default:
                    messageInModal(translate('systmess_error_route_not_found', null, true));

                    break;
            }
        } catch (NotFoundException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_invalid_data', null, true);
                    }
                }
            )));
        } catch (AccessDeniedException | OwnershipException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_permission_not_granted', null, true);
                    }
                }
            )));
        }
    }

    /**
     * Executes actions on profile edit requests by AJAX.
     */
    public function ajax_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();

        $request = request();
        $action = uri()->segment(3);

        try {
            switch ($action) {
                case 'list':
                    checkPermisionAjax('users_administration');

                    $this->showRequestsList($request, $this->getContainer()->get(ProfileEditRequestDatatableRenderer::class));

                    break;
                case 'create':
                    $this->createEditRequest($request, model(Verification_Documents_Model::class));

                    break;
                case 'accept':
                    checkPermisionAjax('users_administration');

                    $this->acceptEditRequest((int) uri()->segment(4) ?: null);

                    break;
                case 'decline':
                    checkPermisionAjax('users_administration');

                    $this->declineEditRequest($request, (int) uri()->segment(4) ?: null);

                    break;
                case 'download':
                    checkPermisionAjax('users_administration');

                    $this->downloadDocumentFile(
                        $this->getContainer()->get(ProfileEditRequestDocumentsService::class),
                        (int) uri()->segment(4) ?: null
                    );

                    break;

                default:
                    jsonResponse(translate('sysmtess_provided_path_not_found'));

                    break;
            }
        } catch (NotFoundException $e) {
            jsonResponse(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_invalid_data', null, true);
                    }
                }
            )));
        } catch (AccessDeniedException | OwnershipException $e) {
            jsonResponse(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_permission_not_granted', null, true);
                    }
                }
            )));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (\Throwable $exception) {
            jsonResponse($exception->getMessage(), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($exception)]
            ));
        }
    }

    /**
     * Shows the list of profile edit requests in Datatables format.
     */
    private function showRequestsList(Request $request, ProfileEditRequestDatatableRenderer $datatableRenderer): void
    {
        $offset = $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0;
        $perPage = $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10;
        $isLegacyMode = 'legacy' === $request->query->get('mode');
        $datatableRenderer->adminGrid(
            $this->requestProvider->paginateForGrid(
                \dtConditions(
                    $isLegacyMode ? $request->request->all() : $request->request->get('filters') ?? [],
                    [
                        ['as' => 'id',               'key' => 'request',       'type' => 'int'],
                        ['as' => 'user',             'key' => 'user',          'type' => 'int'],
                        ['as' => 'search',           'key' => 'search',        'type' => 'mb_substr:0,200'],
                        ['as' => 'status',           'key' => 'status',        'type' => fn (string $v) => EditRequestStatus::from($v)],
                        ['as' => 'createdFromDate',  'key' => 'created_from',  'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'createdToDate',    'key' => 'created_to',    'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'updatedFromDate',  'key' => 'updated_from',  'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'updatedToDate',    'key' => 'updated_to',    'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'acceptedFromDate', 'key' => 'accepted_from', 'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'acceptedToDate',   'key' => 'accepted_to',   'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'declinedFromDate', 'key' => 'declined_from', 'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                        ['as' => 'declinedToDate',   'key' => 'declined_to',   'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)],
                    ]
                ),
                \array_column(
                    \dtOrdering(
                        $request->request->all(),
                        [
                            'request'    => 'id',
                            'createdAt'  => 'created_at_date',
                            'updatedAt'  => 'updated_at_date',
                            'acceptedAt' => 'accepted_at_date',
                            'declinedAt' => 'declined_at_date',
                        ],
                        null,
                        $isLegacyMode
                    ),
                    'direction',
                    'column'
                ),
                $perPage,
                $offset / $perPage + 1,
            ),
            $request->request->getInt('draw'),
            $isLegacyMode
        );
    }

    /**
     * Shows the popup with details for profile edit request.
     */
    private function showDetailsPopup(?int $editRequestId): void
    {
        try {
            $this->requestRenderer->detailsPopup($editRequestId);
        } catch (UserNotFoundException $e) {
            messageInModal($e->getMessage());
        }
    }

    /**
     * Shows the popup with form that allows to decline edit request.
     */
    private function showDeclineFormPopup(?int $editRequestId): void
    {
        try {
            $this->requestRenderer->declinePopup($editRequestId);
        } catch (AccessDeniedException $e) {
            messageInModal($e->getMessage());
        }
    }

    /**
     * Creates profile edit request.
     */
    private function createEditRequest(Request $request, Verification_Documents_Model $documentsRepository): void
    {
        //region Validate
        $userId = (int) privileged_user_id();
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator(
            array_filter([
                new UserNameValidator($adapter),
                $request->request->has('has_legal_name') ? new LegalNameValidator($adapter) : null,
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    ],
                    ['phone' => 'Phone', 'code'  => 'Phone Code'],
                    ['phone' => 'phone', 'code' => 'phone_code']
                ),
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    ],
                    ['phone' => 'Fax', 'code'  => 'Fax code'],
                    ['phone' => 'fax', 'code'  => 'fax_code'],
                    false,
                ),
                new AddressValidator($adapter, [], [], ['city' => 'city', 'state' => 'region', 'postalCode' => 'postal_code']),
                new ReasonValidator($adapter),
                new EditRequestDocumentsValidator($adapter, $documentsRepository, $userId, [DocumentTypeCategory::PERSONAL()]),
            ])
        );
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        try {
            if (!$this->requestProcessingService->canCreateRequest($userId)) {
                jsonResponse(translate('user_preferences_profile_edit_form_request_cannot_be_created_error'), 'error');
            }
            if ($this->requestProcessingService->hasPendingRequest($userId)) {
                jsonResponse(translate('user_preferences_profile_edit_form_request_already_exists'), 'warning');
            }

            // Create the edit request
            $editRequestId = $this->requestProcessingService->createRequest($request, $userId);
            // Notify user about decline
            $this->notifier->send(
                new SystemNotification('profile_edit_request_created', [
                    '[URL]'      => getUrlForGroup("/profile_edit_requests/administration?request={$editRequestId}"),
                    '[USER]'     => $userName = user_name_session(),
                    '[REQUEST]'  => orderNumber($editRequestId),
                    '[USER_URL]' => getUserLink($userName, $userId, (string) userGroupType()),
                ]),
                new RightfulRecipient(['users_administration'])
            );
        } catch (WriteException $e) {
            jsonResponse(translate('user_preferences_profile_edit_form_request_sent_failure'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }

        jsonResponse(translate('user_preferences_profile_edit_form_request_sent_successfullly'), 'success');
    }

    /**
     * Decline the edit request.
     */
    private function declineEditRequest(Request $request, ?int $editRequestId): void
    {
        //region Validate
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new ReasonValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to decline edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        try {
            // Decline request
            $this->requestProcessingService->declineRequest(
                $decliningRequest = $this->requestProvider->getDecliningRequest($editRequestId),
                $declineReason = $request->request->get('reason')
            );
            // Notify user about decline
            $this->notifier->send(
                new SystemNotification('profile_edit_request_decline', [
                    '[REASON]'  => cleanOutput($declineReason),
                    '[REQUEST]' => orderNumber($editRequestId),
                ]),
                new ReferencedRecipient($decliningRequest['id_user'])
            );
        } catch (AccessDeniedException $e) {
            jsonResponse(throwableToMessage($e));
        } catch (WriteException $e) {
            jsonResponse(translate('profile_edit_requests_decline_failure'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }

        jsonResponse(translate('profile_edit_requests_decline_success'), 'success');
    }

    /**
     * Accept the edit request.
     */
    private function acceptEditRequest(?int $editRequestId): void
    {
        try {
            // Get the edit request.
            $acceptedRequest = $this->requestProvider->getPendingRequest($editRequestId);
            // Check if request can be accepted.
            if (!$this->requestProcessingService->canAcceptRequest($acceptedRequest['id_user'])) {
                jsonResponse(translate('user_preferences_profile_edit_form_request_cannot_be_accepted_error'), 'warning');
            }
            // Accept request.
            $this->requestProcessingService->acceptRequest($acceptedRequest);
            // Notify user about decline
            $this->notifier->send(
                new SystemNotification('profile_edit_request_accepted', ['[REQUEST]' => orderNumber($editRequestId)]),
                new ReferencedRecipient($acceptedRequest['id_user'])
            );
            //Update user session
            /** @var TinyMVC_Library_Session $sessionLibrary */
            $sessionLibrary = library(TinyMVC_Library_Session::class);
            $sessionLibrary->updateLoggedUserSession(
                $acceptedRequest['id_user'],
                [
                    'fname' => $acceptedRequest['first_name'],
                    'lname' => $acceptedRequest['last_name'],
                ]
            );
        } catch (ProcessingException $e) {
            jsonResponse(translate('profile_edit_requests_accept_not_processed_documents'), 'warning', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        } catch (NotFoundException $e) {
            jsonResponse(throwableToMessage($e));
        } catch (WriteException $e) {
            jsonResponse(translate('profile_edit_requests_accept_failure'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }

        jsonResponse(translate('profile_edit_requests_accept_success'), 'success');
    }

    /**
     * Download the document file.
     */
    private function downloadDocumentFile(ProfileEditRequestDocumentsService $requestDocumentsService, ?int $documentId): void
    {
        try {
            $token = $requestDocumentsService->getDownloadToken(
                $file = $requestDocumentsService->getDocumentFile($documentId) // Get the remote file
            ); // Get the token for remote file
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (InvalidUuidStringException $exception) {
            jsonResponse(translate('epdocs_download_file_id_invalid_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (UserNotFoundException $exception) {
            jsonResponse(translate('epdocs_download_user_not_found_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (AccessDeniedException $exception) {
            jsonResponse(translate('profile_edit_requests_download_file_invalid_status_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (\App\Plugins\EPDocs\NotFoundException $exception) {
            jsonResponse(translate('epdocs_download_file_not_found_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (OwnershipException $exception) {
            jsonResponse(translate('epdocs_download_third_party_user_file_ownership_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        } catch (\Exception $exception) {
            jsonResponse(translate('epdocs_download_file_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        }

        jsonResponse(null, 'success', [
            'token' => [
                'url'      => config('env.EP_DOCS_HOST', 'http://localhost') . $token->getPath(),
                'name'     => "{$file->getName()}.{$file->getExtension()}",
                'filename' => sprintf(
                    '%s_%s.%s',
                    orderNumber($documentId),
                    (string) u($document['type']['document_title'] ?? $document['internal_name'] ?? $file->getName())->snake(),
                    $file->getExtension()
                ),
            ],
        ]);
    }
}

// End of file profile_edit_requests.php
// Location: /tinymvc/myapp/controllers/profile_edit_requests.php
