<?php

declare(strict_types=1);

use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Http\SignsRequestsTrait;
use App\Common\OAuth2\Exception\InvalidStateException;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\Envelope\Bridge\Datatable\Element\Decorate as DatatableDecorate;
use App\Envelope\Bridge\Datatable\Output\Badge as DatatableBadges;
use App\Envelope\Bridge\Datatable\Output\Button as DatatableButtons;
use App\Envelope\Bridge\DigitalSignature\Command as DigitalSignatureCommands;
use App\Envelope\Bridge\DigitalSignature\Message as DigitalSignatureMessages;
use App\Envelope\Bridge\EPDocs\FileStorage;
use App\Envelope\Bridge\Order\Command as OrderEnvelopeCommands;
use App\Envelope\Bridge\Order\Message as OrderEnvelopeMessages;
use App\Envelope\Bridge\Order\OrderAccessTrait;
use App\Envelope\Command as EnvelopeCommands;
use App\Envelope\EnvelopeAccessTrait;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\EnvelopeTypes;
use App\Envelope\Exception\EnvelopeException;
use App\Envelope\Exception\EnvelopeStatusException;
use App\Envelope\Exception\UpdateRecipientException;
use App\Envelope\Message as EnvelopeMessages;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Envelope\Serial\Command as EnvelopeSerialCommands;
use App\Envelope\SigningMecahisms;
use App\Plugins\Datatable\Output\Button\ActionButton;
use App\Plugins\Datatable\Output\Element as DatatablElements;
use App\Plugins\Datatable\Output\Element\ElementListInterface;
use App\Plugins\Datatable\Output\Template\TemplateInterface;
use App\Plugins\Datatable\Output\Template\TextTemplate;
use App\Plugins\EPDocs\Credentials\JwtCredentials;
use App\Plugins\EPDocs\EPDocsException;
use App\Plugins\EPDocs\Http\Auth;
use App\Plugins\EPDocs\Http\Authentication\Bearer;
use App\Plugins\EPDocs\Rest\RestClient;
use App\Plugins\EPDocs\Storage\JwtTokenStorage;
use App\Validators\AdminRecipientDueDateValidator;
use App\Validators\OrderEnvelopeFilesValidator;
use App\Validators\OrderEnvelopeRecipientsValidator;
use App\Validators\OrderEnvelopeSigningTypeValidator;
use App\Validators\OrderEnvelopeValidator;
use App\Validators\ReasonEnvelopeValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DocuSign\eSign\Client\ApiException;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\NotifierInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Order_Documents_Controller extends TinyMVC_Controller
{
    use OrderAccessTrait;
    use EnvelopeAccessTrait;
    use SignsRequestsTrait;
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    private const SIGNED_URL_CIPHERTEXT_SESSION_KEY = 'appSessionUrlSigningKey';
    private const SIGNED_URL_MAC_QUERY_KEY = 'verificationCode';
    private const SIGNED_URL_STATE_KEY = 'operationState';
    private const SIGNED_URL_TTL = 3600;

    private const MAX_RECIPIENTS = 10;
    private const VIEW_TOKEN_TIMEOUT = 21600;
    private const DEFAULT_DUE_DATE_INTERVAL = 3;

    /**
     * The list of e-signature integrations.
     */
    private array $eSignIntegrations = [
        // Put here the names of the e-signature integrations
        'docusign' => [
            'title'     => 'DocuSign',
            'enabled'   => false,
            'refresh'   => false,
            'connected' => false,
            'oauth2'    => [
                'client' => 'docusign',
            ],
        ],
    ];

    /**
     * Index page.
     */
    public function index(): void
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermisionAndRedirect('monitor_documents', '/order_documents/administration');
        checkPermision('view_documents', '/403');
        checkGroupExpire();

        $data = [
            'title'                 => translate('order_documents_dashboard_page_title_text', null, true),
            'filters'               => with(request(), function (Request $request) {
                $parseId = fn ($id) => null !== $id ? ['value' => (int) toId($id), 'placeholder' => orderNumber((int) toId($id))] : null;

                return [
                    'order'    => $parseId($request->query->get('order') ?? null),
                    'document' => $parseId($request->query->get('document') ?? null),
                ];
            }),
        ];

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->orderDocumentsEpl($data);
        } else {
            $this->orderDocumentsAll($data);
        }
    }

    private function orderDocumentsEpl($data){
        $data['templateViews'] = [
            'mainOutContent'    => 'documents/orders/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function orderDocumentsAll($data){
		views(['new/header_view', 'new/documents/orders/index_view', 'new/footer_view'], $data);
    }

    /**
     * Administration page.
     */
    public function administration(): void
    {
        checkPermision('monitor_documents');

        //region E-Signature integrations
        $oAuth2 = library(TinyMVC_Library_Oauth2::class);
        $integrations = [];
        foreach ($this->eSignIntegrations as $name => $integration) {
            // Enable by default. If something is not right, we will update it later.
            $integration['enabled'] = true;
            $integration['connected'] = true;

            try {
                /** @var TinyMVC_Library_Oauth2 $oAuth2 */
                $client = $oAuth2->client($integration['oauth2']['client'] ?? '');
                $token = $client->getStoredAccessToken();
                if (null === $token) {
                    // Token is not stored in the storage.
                    // So we nead to get it.
                    $integration['connected'] = false;
                } elseif (null !== $token->getExpires() && $token->hasExpired()) {
                    // Token has expired and we need to refresh it.
                    $integration['refresh'] = true;
                }
            } catch (NotFoundException $e) {
                $integration['enabled'] = false;
            }

            $integrations[$name] = $integration;
        }
        //endregion E-Signature integrations

        views(['admin/header_view', 'admin/documents/orders/index_view', 'admin/footer_view'], [
            'integrations'          => $integrations,
            'title'                 => translate('order_documents_dashboard_page_title_text', null, true),
            'filters'               => with(request(), function (Request $request) {
                $parseId = fn ($id) => null !== $id ? ['value' => (int) toId($id), 'text' => orderNumber((int) toId($id))] : [];

                return array_filter(
                    [
                        array_merge(['name' => 'order'], $parseId($request->query->get('order') ?? null)),
                        array_merge(['name' => 'sender'], $parseId($request->query->get('sender') ?? null)),
                        array_merge(['name' => 'document'], $parseId($request->query->get('document') ?? null)),
                        array_merge(['name' => 'recipient'], $parseId($request->query->get('recipient') ?? null)),
                    ],
                    fn (array $filter) => isset($filter['value'])
                );
            }),
        ]);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        checkIsAjax();
        checkIsLogged();
        checkDomainForGroup();
        checkGroupExpire('modal');

        /** @var Orders_Model $orders */
        $orders = model(Orders_Model::class);
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        /** @var int $userId */
        $userId = (int) privileged_user_id();
        /** @var null|int $pathParameter */
        $pathParameter = (int) uri()->segment(4) ?: null;

        try {
            switch (uri()->segment(3)) {
                case 'create-envelope':
                    checkPermisionAjaxModal('create_document');

                    $this->showCreateEnvelopePopup($orders, $userId, $pathParameter);

                    break;

                case 'edit-envelope':
                    checkPermisionAjaxModal('edit_document');

                    $this->showEditEnvelopePopup($envelopes, $orders, $userId, $pathParameter);

                    break;

                case 'sign-envelope':
                    checkPermisionAjaxModal('process_document');

                    $this->showSignEnvelopePopup($envelopes, $userId, $pathParameter);

                    break;

                case 'void-envelope':
                    checkPermisionAjaxModal('delete_document');

                    $this->showVoidEnvelopePopup($envelopes, $userId, $pathParameter);

                    break;

                case 'list-envelopes':
                    checkPermisionAjaxModal('view_documents');

                    $this->showListEnvelopesPopup($orders, $userId, $pathParameter);

                    break;

                case 'decline-envelope':
                    checkPermisionAjaxModal('process_document');

                    $this->showDeclineEnvelopePopup($envelopes, $userId, $pathParameter);

                    break;

                case 'view-envelope-details':
                    checkPermisionAjaxModal('read_document');

                    $this->showDetailsEnvelopePopup($envelopes, $orders, $userId, $pathParameter);

                    break;

                case 'decline-signed-envelope':
                    checkPermisionAjaxModal('process_document');

                    $this->showDeclineSignedEnvelopePopup($envelopes, $userId, $pathParameter);

                    break;

                case 'forward-latest-envelope':
                    checkPermisionAjaxModal('create_document');

                    $this->showCopyEnvelopePopup($envelopes, $orders, $userId, $pathParameter, false);

                    break;

                case 'copy-original-envelope':
                    checkPermisionAjaxModal('create_document');

                    $this->showCopyEnvelopePopup($envelopes, $orders, $userId, $pathParameter, true);

                    break;

                case 'update-envelope-info':
                    checkPermisionAjaxModal('edit_document');

                    $this->showUpdateEnvelopeDisplayInfoPopup($envelopes, $userId, $pathParameter);

                    break;

                default:
                    messageInModal(translate('systmess_error_route_not_found', null, true));
            }
        } catch (NotFoundException $e) {
            messageInModal(throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        } catch (AccessDeniedException | OwnershipException $e) {
            messageInModal(throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }
    }

    /**
     * Shows popup forms for admin.
     */
    public function popup_admin_forms(): void
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjaxModal('monitor_documents');

        /** @var Orders_Model $orders */
        $orders = model(Orders_Model::class);
        /** @var null|n $pathParameter */
        $pathParameter = (int) uri()->segment(4) ?: null;

        try {
            switch (uri()->segment(3)) {
                case 'list-envelopes':
                    $this->showListEnvelopesAdminPopup($orders, $pathParameter);

                    break;

                case 'edit-due-dates':
                    checkPermisionAjaxModal('edit_due_date_administration');

                    /** @var Envelopes_Model $envelopes */
                    $envelopes = model(Envelopes_Model::class);

                    $this->showEditDueDatesAdminPopup($envelopes, $orders, $pathParameter);

                    break;

                default:
                    messageInModal(translate('systmess_error_route_not_found', null, true));
            }
        } catch (NotFoundException $e) {
            messageInModal(throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        } catch (AccessDeniedException | OwnershipException $e) {
            messageInModal(throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }
    }

    /**
     * Executes actions on personal documents by AJAX.
     */
    public function ajax_operation(): void
    {
        checkIsAjax();
        checkIsLogged();
        checkDomainForGroup();
        checkGroupExpire('ajax');

        $request = request();
        /** @var Orders_Model $orders */
        $orders = model(Orders_Model::class);
        /** @var int $userId */
        $userId = (int) privileged_user_id();

        try {
            switch (uri()->segment(3)) {
                case 'create-envelope':
                    checkPermisionAjax('create_document');

                    $this->createEnvelope($request, $orders, $userId, $request->request->getInt('order') ?: null);

                    break;

                case 'edit-envelope':
                    checkPermisionAjax('edit_document');

                    $this->editEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'copy-envelope':
                    checkPermisionAjax('edit_document');

                    $this->copyEnvelope(
                        $request,
                        $orders,
                        $userId,
                        $request->request->getInt('envelope') ?: null,
                        $request->request->getInt('order') ?: null
                    );

                    break;

                case 'send-envelope':
                    checkPermisionAjax('process_document');

                    $this->sendEnvelope($userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'sign-envelope':
                    checkPermisionAjax('process_document');

                    $this->signEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'view-envelope':
                    checkPermisionAjax('read_document');

                    $this->viewEnvelope($userId, $request->request->getInt('envelope') ?: null, $request->request->getInt('document') ?: null);

                    break;

                case 'list-envelopes':
                    checkPermisionAjax('view_documents');

                    $this->listEnvelopes(
                        $request,
                        $userId,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10,
                        'legacy' === $request->request->get('mode')
                    );

                    break;

                case 'void-envelope':
                    checkPermisionAjax('delete_document');

                    $this->voidEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'require-approval':
                    checkPermisionAjax('process_document');

                    $this->requireEnvelopeApproval($userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'download-document':
                    checkPermisionAjax('read_document');

                    $this->downloadDocuments($userId, $request->request->getInt('envelope') ?: null, $request->request->getInt('document') ?: null);

                    break;

                case 'decline-envelope':
                    checkPermisionAjax('process_document');

                    $this->declineEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'confirm-envelope':
                    checkPermisionAjax('process_document');

                    $this->confirmSignedEnvelope($userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'decline-signed-envelope':
                    checkPermisionAjax('process_document');

                    $this->declineSignedEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'list-detached-envelopes':
                    checkPermisionAjax('view_documents');

                    $this->listDetachedEnvelopes(
                        $request,
                        $userId,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10,
                        'legacy' === $request->request->get('mode')
                    );

                    break;

                case 'access-remote-envelope':
                    checkPermisionAjax('process_document');

                    $this->accessRemoteEnvelope($request, $userId, $request->request->getInt('envelope') ?: null);

                    break;

                case 'update-envelope-info':
                    checkPermisionAjax('edit_document');

                    $this->editEnvelopeDisplayInfo(
                        $request,
                        $userId,
                        $request->request->getInt('envelope') ?: null
                    );

                    break;

                default:
                    json(['message' => translate('systmess_error_route_not_found', null, true), 'mess_type' => 'error'], 404);
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
     * Executes admin actions on personal documents by AJAX.
     */
    public function ajax_admin_operation(): void
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjax('monitor_documents');

        $request = request();
        /** @var int $userId */
        $userId = (int) privileged_user_id();

        try {
            switch (uri()->segment(3)) {
                case 'list-envelopes':
                    $this->listAdminEnvelopes(
                        $request,
                        $userId,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10,
                        'legacy' === $request->request->get('mode')
                    );

                    break;

                case 'open-document-edit-mode':
                    $this->openEnvelopeEditMode($request, $request->request->getInt('envelope') ?: null);

                    break;

                case 'save-due-dates':
                    $this->saveDueDatesByAdmin($request, $request->request->getInt('envelope'));

                    break;

                case 'download-document':
                    $this->downloadDocuments(
                        $userId,
                        $request->request->getInt('envelope') ?: null,
                        $request->request->getInt('document') ?: null,
                        false
                    );

                    break;

                case 'list-detached-envelopes':
                    $this->listAdminDetachedEnvelopes(
                        $request,
                        $userId,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10,
                        'legacy' === $request->request->get('mode')
                    );

                    break;

                default:
                    json(['message' => translate('systmess_error_route_not_found'), 'mess_type' => 'error'], 404);
            }
        } catch (NotFoundException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_invalid_data')),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (AccessDeniedException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_permission_not_granted')),
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
     * Starts the envelope edit mode.
     */
    public function start_envelope_edit(): void
    {
        checkPermision('monitor_documents');
        // TODO: do something with this
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        $envelopeId = uri()->segment(3) ?? null;
        if (
            null === $envelopeId
            || null === $envelopes->findOne($envelopeId)
        ) {
            redirectWithMessage('/404', throwableToMessage(
                new NotFoundException(sprintf('The envelope with ID "%s" is not found', $envelopeId)),
                translate('systmess_error_invalid_data', null, true)
            ));
        }

        views()->display('admin/documents/orders/start_edit_mode_view', [
            'url'        => getUrlForGroup('/order_documents/ajax_admin_operation/open-document-edit-mode'),
            'envelopeId' => $envelopeId,
        ]);
    }

    /**
     * Finalizes the envelope edit mode.
     */
    public function finalize_envelope_edit(): void
    {
        try {
            $this->verifySignedRequest($request = request());

            (new DigitalSignatureCommands\CommitEnvelopeEditMode(
                model(Envelopes_Model::class),
                $this->getContainer()->get(NotifierInterface::class)
            ))->__invoke(
                new DigitalSignatureMessages\CommitEnvelopeEditModeMessage($request->query->get('originalEnvelopeId') ?? null)
            );
        } catch (NotFoundException $e) {
            redirectWithMessage('/404', throwableToMessage($e, translate('systmess_error_invalid_data', null, true)));
        } catch (Exception | DomainException | EnvelopeException $e) {
            redirectWithMessage('/403', throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }

        views()->display('admin/documents/orders/finalize_edit_mode_view');
    }

    /**
     * Finalizes the envelope preview.
     */
    public function finalize_envelope_preview(): void
    {
        try {
            $this->verifySignedRequest($request = request());
        } catch (AccessDeniedException $e) {
            redirectWithMessage('/403', throwableToMessage($e, translate('systmess_error_permission_not_granted', null, true)));
        }

        // TODO: do something with this
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        $envelopeUuid = $request->query->get('originalEnvelopeId') ?? null;
        if (
            null === $envelopeUuid
            || null === $envelopes->findOneBy(['conditions' => ['uuid' => $envelopeUuid]])
        ) {
            redirectWithMessage('/404', throwableToMessage(
                new NotFoundException(sprintf('The envelope with UUID "%s" is not found', $envelopeUuid)),
                translate('systmess_error_invalid_data', null, true)
            ));
        }

        switch ($request->query->get('event') ?? null) {
            case 'viewing_complete':
            case 'signing_complete':
                $message = translate('order_documents_digital_interaction_message_success_text', null, true);

                break;

            case 'canceled':
            default:
                $message = null;

                break;
        }

        if (null === $message) {
            redirect('/order_documents');
        }

        redirectWithMessage('/order_documents', $message, 'success');
    }

    /**
     * Shows the popup where user can create document envelopes.
     */
    protected function showDetailsEnvelopePopup(Envelopes_Model $envelopes, Orders_Model $orders, int $userId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForDetails($envelopeId);
        // Get the order
        $order = $orders->findWithAssignees(
            (int) ($envelope['order_reference']['id_order'] ?? null) ?: null
        );
        //endregion Entities

        //region Access Check
        $this->assertCanViewEnvelopeDetails($userId, $envelope);
        //endregion Access Check

        //region Prepare Parts
        //region Recipients
        $assignees = $this->getNormalizedOrderAssignees($order);
        $recipients = $this->prepareRecipientForDetailsPreview($envelope['recipients'] ?? new ArrayCollection(), $assignees);
        //endregion Recipients

        //region Documents
        $documents = (new ArrayCollection(
            [
                ['original', $envelope['documents']['original'] ?? null],
                ['latest', $envelope['documents']['latest'] ?? null],
            ]
        ))
            ->filter(fn (array $entry) => null !== ($entry[1] ?? null))
            ->map(fn (array $entry)    => [
                $entry[0],
                array_merge($entry[1], [
                    'status_badge'   => arrayGet(
                        [
                            'original'   => [
                                'text'  => translate('order_documents_dashboard_details_popup_description_badge_original_text', null, true),
                                'color' => 'badge-success',
                            ],
                            'latest'      => [
                                'text'  => translate('order_documents_dashboard_details_popup_description_badge_latest_text', null, true),
                                'color' => 'badge-primary',
                            ],
                        ],
                        $entry[0]
                    ),
                ]),
            ])
        ;
        //endregion Documents
        // //endregion Prepare Parts

        views()->display('new/documents/orders/popups/details_view', arrayCamelizeAssocKeys([
            'is_sender'  => $envelope['id_sender'] === $userId,
            'envelope'   => $envelope,
            'assignees'  => $assignees,
            'documents'  => $documents,
            'recipients' => $recipients->toArray(),
        ]));
    }

    /**
     * Shows the popup where user can create document envelopes.
     */
    protected function showListEnvelopesPopup(Orders_Model $orders, int $userId, ?int $orderId): void
    {
        //region Entities
        // Get the order
        $order = $orders->findWithAssignees($orderId);
        //endregion Entities

        // Check if sender has access to order
        $this->assertSenderHasAccessToOrder($userId, $order);

        views()->display('new/documents/orders/popups/list_view', arrayCamelizeAssocKeys([
            'order'  => [
                'id' => $orderId,
            ],
        ]));
    }

    /**
     * Shows the popup where user can create document envelopes.
     */
    protected function showListEnvelopesAdminPopup(Orders_Model $orders, ?int $orderId): void
    {
        //region Entities
        // Get the order
        $order = $orders->findWithAssignees($orderId);
        //endregion Entities

        views()->display('admin/documents/orders/popups/list_view', arrayCamelizeAssocKeys([
            'order'  => [
                'id' => $orderId,
            ],
        ]));
    }

    /**
     * Shows the popup where user can create document envelopes.
     */
    protected function showEditDueDatesAdminPopup(Envelopes_model $envelopes, Orders_Model $orders, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForEdit($envelopeId, true);

        // Get the order
        $order = $orders->findWithAssignees(
            (int) ($envelope['order_reference']['id_order'] ?? null) ?: null
        );

        //region Recipients
        $assignees = $this->getNormalizedOrderAssignees($order);
        //endregion Recipients
        //endregion Entities

        views()->display('admin/documents/orders/popups/edit_due_dates_view', arrayCamelizeAssocKeys([
            'assignees'      => $assignees,
            'envelope'       => $envelope,
            'url'            => __SITE_URL . '/order_documents/ajax_admin_operation/save-due-dates',
            'order'          => $order,
            'recipients'     => $envelope['recipients']->toArray(),
        ]));
    }

    /**
     * Shows the popup where user can create document envelopes.
     */
    protected function showCreateEnvelopePopup(Orders_Model $orders, int $senderId, ?int $orderId): void
    {
        //region Entities
        // Get the order
        $order = $orders->findWithAssignees($orderId);
        //endregion Entities

        // Check if sender has access to order
        $this->assertSenderHasAccessToOrder($senderId, $order);

        views()->display('new/documents/orders/popups/envelope_form_view', arrayCamelizeAssocKeys([
            'order'                      => ['id' => $orderId],
            'url'                        => getUrlForGroup('/order_documents/ajax_operation/create-envelope'),
            'download'                   => $this->getUploadOptions(),
            'assignees'                  => $this->getNormalizedOrderAssignees($order),
            'max_recipients'             => static::MAX_RECIPIENTS,
            'signing_mechanism'          => SigningMecahisms::NATIVE,
            'default_due_date_interval'  => static::DEFAULT_DUE_DATE_INTERVAL,
            'max_due_days'               => (int) config('envelope_document_max_calendar_days', 60),
            'locales'                    => [
                'form'  => [
                    'button' => 'order_documents_process_form_create_button_text',
                ],
                'files' => [
                    'button_text'         => 'general_dashboard_modal_field_document_help_text_line_1',
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate',
                ],
            ],
        ]));
    }

    /**
     * Shows the popup where user can edit document envelopes.
     */
    protected function showEditEnvelopePopup(Envelopes_Model $envelopes, Orders_Model $orders, int $senderId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForEdit($envelopeId, true);
        // Get the order
        $order = $orders->findWithAssignees(
            $orderId = (int) ($envelope['order_reference']['id_order'] ?? null) ?: null
        );
        //endregion Entities

        //region Access Check
        $this->assertSenderIsEnvelopeOwner($senderId, $envelope);
        $this->assertEnvelopeIsEditable($envelope);
        //endregion Access Check

        //region Prepare Parts
        //region Recipients
        $assignees = $this->getNormalizedOrderAssignees($order);
        $recipients = $this->prepareRecipientForEditPreview($envelope['recipients'] ?? new ArrayCollection(), $assignees);
        //endregion Recipients

        //region Files
        /** @var Collection $files */
        $files = $this->prepareDocumentsForEditPreview($envelope['documents'] ?? new ArrayCollection());
        //endregion Files
        //endregion Prepare Parts

        views()->display('new/documents/orders/popups/envelope_form_view', arrayCamelizeAssocKeys([
            'order'                      => ['id' => $orderId],
            'url'                        => getUrlForGroup('/order_documents/ajax_operation/edit-envelope'),
            'files'                      => $files->toArray(),
            'download'                   => $this->getUploadOptions(count($files)),
            'envelope'                   => $envelope,
            'assignees'                  => $assignees,
            'recipients'                 => $recipients->toArray(),
            'max_recipients'             => static::MAX_RECIPIENTS,
            'signing_mechanism'          => $envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE,
            'default_due_date_interval'  => static::DEFAULT_DUE_DATE_INTERVAL,
            'max_due_days'               => (int) config('envelope_document_max_calendar_days', 60),
            'locales'                    => [
                'form'  => [
                    'button' => 'order_documents_process_form_edit_button_text',
                ],
                'files' => [
                    'button_text'         => 'general_dashboard_modal_field_document_help_text_line_1',
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate',
                ],
            ],
        ]));
    }

    /**
     * Shows the popup where user can edit document envelope description.
     */
    protected function showUpdateEnvelopeDisplayInfoPopup(Envelopes_Model $envelopes, int $senderId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForEdit($envelopeId);
        //endregion Entities

        //region Access Check
        $this->assertSenderIsEnvelopeOwner($senderId, $envelope);
        $this->assertCanEditEnvelopeDisplayInfo($envelope);
        //endregion Access Check

        views()->display('new/documents/orders/popups/envelope_info_form_view', arrayCamelizeAssocKeys([
            'url'        => getUrlForGroup('/order_documents/ajax_operation/update-envelope-info'),
            'envelope'   => $envelope,
        ]));
    }

    /**
     * Shows the popup where user can copy the envelope.
     */
    protected function showCopyEnvelopePopup(Envelopes_Model $envelopes, Orders_Model $orders, int $senderId, ?int $envelopeId, bool $isOriginal): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForCopy($envelopeId);
        // Get the order
        $order = $orders->findWithAssignees(
            $orderId = (int) ($envelope['order_reference']['id_order'] ?? null) ?: null
        );
        //endregion Entities

        //region Access Check
        $this->assertSenderIsEnvelopeOwner($senderId, $envelope);
        //endregion Access Check

        //region Prepare Parts
        //region Recipients
        $recipientsRouting = $envelope['recipients_routing'] ?? [];
        $assignees = $this->getNormalizedOrderAssignees($order);
        $recipients = $this->prepareRecipientForEditPreview($recipientsRouting['recipients'] ?? new ArrayCollection(), $assignees);
        //endregion Recipients

        //region Files
        /** @var Collection $documents */
        $documents = $envelope['documents'] ?? new ArrayCollection();
        list($originals, $submittedDocuments) = $documents->partition(fn ($key, array $document) => $document['is_authoriative_copy']);

        if ($isOriginal) {
            // Prepare files information for view
            $files = $this->prepareDocumentsForEditPreview($originals);
        } else {
            /** @var Collection $routingPipeline */
            $routingPipeline = $recipientsRouting['routing'] ?? new ArrayCollection(); // Get all routing for envelope
            if (!$routingPipeline->forAll(fn ($i, Collection $step) => 1 === $step->count())) {
                // If at least one of the step is parallel the process will stop
                throw new AccessDeniedException('This action is not supported for parallel recipients routing.');
            }

            /** @var null|Collection $currentRouting */
            $currentRouting = $recipientsRouting['current_routing'] ?? null; // Get current routing
            $currentRecipient = $currentRouting->first(); // Find first recipient
            $finalDocument = $submittedDocuments->last() ?: null; // Get the last one from submitted documents

            // If recipient is signer with not completed document
            // then we will take the penultimate document (if exists)
            if (
                RecipientTypes::SIGNER === $currentRecipient['type']
                && in_array($currentRecipient['status'], [RecipientStatuses::SIGNED, RecipientStatuses::DECLINED])
            ) {
                // Here will go only if signer has either waiting for confirmation or was declined
                $latestDocument = $submittedDocuments->filter(fn (array $document) => $document !== $finalDocument)->last() ?: null;
            } else {
                // Else the final one will do
                $latestDocument = $finalDocument;
            }

            // And now prepare files information for view
            $files = $this->prepareDocumentsForEditPreview(
                // If we didn't find the latest document, then the original(s) will be used
                empty($latestDocument) ? $originals : new ArrayCollection([$latestDocument])
            );
        }
        //endregion Files
        //endregion Prepare Parts

        views()->display('new/documents/orders/popups/envelope_form_view', arrayCamelizeAssocKeys([
            'url'                       => getUrlForGroup('/order_documents/ajax_operation/copy-envelope'),
            'order'                     => ['id' => $orderId],
            'files'                     => $files->toArray(),
            'download'                  => $this->getUploadOptions(count($files)),
            'envelope'                  => $envelope,
            'assignees'                 => $assignees,
            'recipients'                => $recipients->toArray(),
            'max_recipients'            => static::MAX_RECIPIENTS,
            'signing_mechanism'         => $envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE,
            'default_due_date_interval' => static::DEFAULT_DUE_DATE_INTERVAL,
            'max_due_days'              => (int) config('envelope_document_max_calendar_days', 60),
            'locales'                   => [
                'form'  => [
                    'button' => 'order_documents_copy_form_copy_button_text',
                ],
                'files' => [
                    'button_text'         => 'general_dashboard_modal_field_document_help_text_line_1',
                    'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                    'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                    'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                    'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate',
                ],
            ],
        ]));
    }

    /**
     * Shows the popup where user can upload the signed document.
     */
    protected function showSignEnvelopePopup(Envelopes_Model $envelopes, int $userId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForSigning($envelopeId);
        //endregion Entities

        //region Access Check
        $this->assertCanSignOrDeclineEnvelope($userId, $envelope);
        //endregion Access Check

        try {
            // Mark recipient as delivered, if needed
            (new EnvelopeCommands\DeliverEnvelopeToRecipient(model(Envelopes_Model::class)))->__invoke(
                new EnvelopeMessages\DeliverEnvelopeMessage($envelope['id'], $userId, $envelope['current_routing_order'])
            );
        } catch (Throwable $e) {
            // TODO: log this exception
        }

        views()->display('new/documents/orders/popups/sign_form_view', arrayCamelizeAssocKeys([
            'url'        => getUrlForGroup('/order_documents/ajax_operation/sign-envelope'),
            'envelope'   => $envelope,
            'download'   => $this->getUploadOptions(),
            'locales'    => [
                'size_text'           => 'general_dashboard_modal_field_document_help_text_line_1',
                'format_text'         => 'general_dashboard_modal_field_document_help_text_line_3',
                'amount_text'         => 'general_dashboard_modal_field_document_help_text_line_2',
                'limited_amount_text' => 'general_dashboard_modal_field_document_help_text_line_2_limited_alternate',
            ],
        ]));
    }

    /**
     * Shows the popup where recipient can decline the document signing.
     */
    protected function showDeclineEnvelopePopup(Envelopes_Model $envelopes, int $userId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForSigning($envelopeId);
        //endregion Entities

        //region Access Check
        // Given that the conditions for signing are the same as for declining, we can safelly use it here.
        $this->assertCanSignOrDeclineEnvelope($userId, $envelope);
        //endregion Access Check

        try {
            // Mark recipient as delivered, if needed
            (new EnvelopeCommands\DeliverEnvelopeToRecipient(model(Envelopes_Model::class)))->__invoke(
                new EnvelopeMessages\DeliverEnvelopeMessage($envelope['id'], $userId, $envelope['current_routing_order'])
            );
        } catch (Throwable $e) {
            // TODO: log this exception
        }

        views()->display('new/documents/orders/popups/decline_form_view', arrayCamelizeAssocKeys([
            'url'      => getUrlForGroup('/order_documents/ajax_operation/decline-envelope'),
            'envelope' => $envelope,
        ]));
    }

    /**
     * Shows the popup where sender can decline the signed document.
     */
    protected function showDeclineSignedEnvelopePopup(Envelopes_Model $envelopes, int $userId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForSigning($envelopeId);
        //endregion Entities

        //region Access Check
        // Given that the conditions for signing are the same as for declining, we can safelly use it here.
        $this->assertEnvelopeIsActive($envelope);
        $this->assertSenderIsEnvelopeOwner($userId, $envelope);
        $this->assertCanDeclineOrConfirmSignedEnvelope($userId, $envelope);
        //endregion Access Check

        views()->display('new/documents/orders/popups/decline_form_view', arrayCamelizeAssocKeys([
            'url'      => getUrlForGroup('/order_documents/ajax_operation/decline-signed-envelope'),
            'envelope' => $envelope,
        ]));
    }

    /**
     * Shows the popup where sender can void envelope.
     */
    protected function showVoidEnvelopePopup(Envelopes_Model $envelopes, int $userId, ?int $envelopeId): void
    {
        //region Entities
        // Get the envelope
        $envelope = $envelopes->findForSigning($envelopeId);
        //endregion Entities

        //region Access Check
        $this->assertSenderIsEnvelopeOwner($userId, $envelope);
        $this->assertEnvelopeIsVoidable($envelope);
        //endregion Access Check

        views()->display('new/documents/orders/popups/void_form_view', arrayCamelizeAssocKeys([
            'url'      => getUrlForGroup('/order_documents/ajax_operation/void-envelope'),
            'envelope' => $envelope,
        ]));
    }

    protected function saveDueDatesByAdmin(Request $request, int $envelopeId): void
    {
        $recipientsList = json_decode($request->request->get('dates'), true);

        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AdminRecipientDueDateValidator($adapter, $recipientsList);
        if (!$validator->validate(request()->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        try {
            (
                new EnvelopeCommands\ExtendDueDateDraft(
                    model(Envelope_Recipients_Model::class)
                )
            )->__invoke(
                new EnvelopeMessages\ExtendDueDatesMessage(
                    $envelopeId,
                    $recipientsList
                )
            );
        } catch (UpdateRecipientException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_update_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_update_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Creates the envelope.
     *
     * @throws AccessDeniedException if sender has no access to the order
     */
    protected function createEnvelope(Request $request, Orders_Model $orders, int $senderId, ?int $orderId): void
    {
        //region Entity
        // Get the order
        $order = $orders->findWithAssignees($orderId);
        //endregion Entity

        // Check if sender has access to order
        $this->assertSenderHasAccessToOrder($senderId, $order);

        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator([
            new OrderEnvelopeValidator($adapter),
            new OrderEnvelopeFilesValidator($adapter),
            new OrderEnvelopeRecipientsValidator($adapter, model(User_Model::class), (array) $request->request->get('recipients'), static::MAX_RECIPIENTS),
            new OrderEnvelopeSigningTypeValidator($adapter, [SigningMecahisms::NATIVE, SigningMecahisms::DOCUSIGN]),
        ]);
        if (!$validator->validate(request()->request->all())) {
            throw new ValidationException(translate('systmess_error_validation_add_order_document'), 0, null, $validator->getViolations());
        }
        //endregion Validation

        try {
            // Create envelope
            $envelopeId = (
                new OrderEnvelopeCommands\CreateOrderEnvelopeDraft(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new OrderEnvelopeMessages\CreateOrderEnvelopeDraftMessage(
                    (int) $order['id'],
                    $senderId,
                    $request->request->get('title'),
                    $request->request->get('type'),
                    $request->request->get('description'),
                    EnvelopeTypes::PERSONAL,
                    $request->request->get('signing_type') ?? SigningMecahisms::NATIVE,
                    (array) $request->request->get('properties', []),
                    (array) $request->request->get('recipients', []),
                    $request->request->get('files') ?? []
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_create_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_create_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Edits the envelope.
     */
    protected function editEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator(array_filter([
            new OrderEnvelopeValidator($adapter),
            new OrderEnvelopeRecipientsValidator($adapter, model(User_Model::class), (array) $request->request->get('recipients'), static::MAX_RECIPIENTS),
            !$request->request->has('old-files') ? new OrderEnvelopeFilesValidator($adapter) : null,
            new OrderEnvelopeSigningTypeValidator($adapter, [SigningMecahisms::NATIVE, SigningMecahisms::DOCUSIGN]),
        ]));

        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        try {
            // Update envelope
            (
                new OrderEnvelopeCommands\UpdateOrderEnvelopeDraft(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new OrderEnvelopeMessages\UpdateOrderEnvelopeDraftMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('title'),
                    $request->request->get('type'),
                    $request->request->get('description'),
                    $request->request->get('signing_type') ?? SigningMecahisms::NATIVE,
                    (array) $request->request->get('properties', []),
                    (array) $request->request->get('recipients', []),
                    $request->request->get('files') ?? []
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_update_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_update_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Creates copy of the envelope.
     */
    protected function copyEnvelope(Request $request, Orders_Model $orders, int $senderId, ?int $envelopeId, ?int $orderId): void
    {
        //region Entity
        // Get the order
        $order = $orders->findWithAssignees($orderId);
        //endregion Entity

        // Check if sender has access to order
        $this->assertSenderHasAccessToOrder($senderId, $order);

        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator(array_filter([
            new OrderEnvelopeValidator($adapter),
            new OrderEnvelopeRecipientsValidator($adapter, model(User_Model::class), (array) $request->request->get('recipients'), static::MAX_RECIPIENTS),
            !$request->request->has('old-files') ? new OrderEnvelopeFilesValidator($adapter) : null,
        ]));

        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        try {
            // Copy envelope
            $copiedEnvelopeId = (
                new OrderEnvelopeCommands\CopyEnvelope(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new OrderEnvelopeMessages\CopyEnvelopeMessage(
                    (int) $order['id'],
                    $envelopeId,
                    $senderId,
                    $request->request->get('title'),
                    $request->request->get('type'),
                    $request->request->get('description'),
                    $request->request->get('signing_type') ?? SigningMecahisms::NATIVE,
                    $request->request->get('properties') ?? [],
                    $request->request->get('recipients') ?? [],
                    $request->request->get('old-files') ?? [],
                    $request->request->get('files') ?? []
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_copy_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_copy_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $copiedEnvelopeId,
            ],
        ]);
    }

    /**
     * Edits the envelope display info.
     */
    protected function editEnvelopeDisplayInfo(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new OrderEnvelopeValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Update
        try {
            // Propagate the envelope routing
            (
                new EnvelopeCommands\UpdateEnvelopeDisplayInformation(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\UpdateEnvelopeDisplayInformationMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('title'),
                    $request->request->get('type'),
                    $request->request->get('description')
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_update_info_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Update

        jsonResponse(translate('order_documents_update_info_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Sends the envelope to the recipients.
     */
    protected function sendEnvelope(int $senderId, ?int $envelopeId): void
    {
        try {
            // Propagate the envelope routing
            (
                new EnvelopeCommands\SendEnvelope(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke((new EnvelopeMessages\SendEnvelopeMessage($envelopeId, $senderId, true))->withAccessRulesList(['monitor_documents']));
        } catch (DomainException | EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_send_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_send_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Require approval for the envelope.
     */
    protected function requireEnvelopeApproval(int $senderId, ?int $envelopeId): void
    {
        try {
            // Require approval for digital signing
            (
                new OrderEnvelopeCommands\RequireEnvelopeApproval(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new OrderEnvelopeMessages\RequireEnvelopeApprovalMessage($envelopeId, $senderId))->withAccessRulesList(['monitor_documents'])
            );
        } catch (DomainException | EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_require_approval_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(translate('order_documents_send_with_completion_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Opens digital envelope edit mode if supported.
     */
    protected function openEnvelopeEditMode(Request $request, ?int $envelopeId): void
    {
        try {
            // Require approval for digital signing
            list('url' => $redirectUrl) = (
                new DigitalSignatureCommands\OpenEnvelopeEditMode(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    library(TinyMVC_Library_Digital_Signatures::class)
                )
            )->__invoke(new DigitalSignatureMessages\OpenEnvelopeEditModeMessage(
                $envelopeId,
                $this->signRedirectResponse(
                    $request,
                    new RedirectResponse(getUrlForGroup('/order_documents/finalize_envelope_edit')),
                    static::SIGNED_URL_TTL
                )->getTargetUrl()
            ));
        } catch (DomainException | EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_start_edit_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (ApiException | InvalidStateException $e) {
            jsonResponse(
                throwableToMessage($e, sprintf('The operation failed with error: %s', $e->getMessage())),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }

        jsonResponse(null, 'success', arrayCamelizeAssocKeys([
            'redirect_url' => $redirectUrl,
        ]));
    }

    /**
     * Returns URL that grants remote access to the envelope.
     */
    protected function accessRemoteEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Access envelope
        try {
            $accessUrl = (new DigitalSignatureCommands\OpenEnvelopeView(
                model(Envelopes_Model::class),
                library(TinyMVC_Library_Digital_Signatures::class)
            ))->__invoke(new DigitalSignatureMessages\OpenEnvelopeViewMessage(
                $envelopeId,
                $senderId,
                config('env.DOCUSIGN_SECURITY_DOMAIN'),
                $this->signRedirectResponse(
                    $request,
                    new RedirectResponse(getUrlForGroup('/order_documents/finalize_envelope_preview')),
                    static::SIGNED_URL_TTL
                )->getTargetUrl()
            ));
        } catch (EnvelopeStatusException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_access_action_message_invalid_status_error_text', null, true)),
                'warning',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_access_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Access envelope

        jsonResponse(null, 'success', arrayCamelizeAssocKeys([
            'redirect_url' => $accessUrl,
        ]));
    }

    /**
     * Signs the envelope.
     */
    protected function signEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new OrderEnvelopeFilesValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Sign
        try {
            // Propagate the envelope routing
            (
                new EnvelopeSerialCommands\SignEnvelope(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\SignEnvelopeMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('files') ?? []
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_sign_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Sign

        jsonResponse(translate('order_documents_sign_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Signs the envelope.
     */
    protected function viewEnvelope(int $userId, ?int $envelopeId, ?int $documentId): void
    {
        //region View
        try {
            // Access the documents
            $accessToken = (
                new EnvelopeCommands\AccessDocument(
                    model(Envelope_Documents_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    )
                )
            )->__invoke(
                new EnvelopeMessages\AccessDocumentMessage(
                    $envelopeId,
                    $userId,
                    $documentId,
                    static::VIEW_TOKEN_TIMEOUT
                )
            );

            // View the envelope
            (
                new EnvelopeCommands\ViewEnvelope(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\ViewEnvelopeMessage($envelopeId, $userId))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_view_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion View

        jsonResponse(null, 'success', [
            'preview' => [
                'targets'             => array_map(
                    fn (array $token) => config('env.EP_DOCS_HOST', 'http://localhost') . $token['preview'],
                    [$accessToken]
                ),
            ],
        ]);
    }

    /**
     * Declines the envelope.
     */
    protected function declineEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new ReasonEnvelopeValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Decline
        try {
            // Propagate the envelope routing
            (
                new EnvelopeCommands\DeclineEnvelopeSigning(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\DeclineEnvelopeSigningMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('reason')
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_decline_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Decline

        jsonResponse(translate('order_documents_decline_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Declines the envelope.
     */
    protected function declineSignedEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new ReasonEnvelopeValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Decline
        try {
            // Propagate the envelope routing
            (
                new EnvelopeSerialCommands\DeclineSignedEnvelope(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\DeclineSignedEnvelopeMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('reason')
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_decline_signed_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Decline

        jsonResponse(translate('order_documents_decline_signed_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Declines the envelope.
     */
    protected function confirmSignedEnvelope(int $senderId, ?int $envelopeId): void
    {
        //region Confirm
        try {
            // Propagate the envelope routing
            (
                new EnvelopeSerialCommands\ConfirmSignedEnvelope(
                    model(Envelopes_Model::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\ConfirmSignedEnvelopeMessage($envelopeId, $senderId))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_confirm_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Confirm

        jsonResponse(translate('order_documents_confirm_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Voids the envelope.
     */
    protected function voidEnvelope(Request $request, int $senderId, ?int $envelopeId): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new ReasonEnvelopeValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Void
        try {
            // Propagate the envelope routing
            (
                new EnvelopeCommands\VoidEnvelope(
                    model(Envelopes_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    library(TinyMVC_Library_Digital_Signatures::class),
                    $this->getContainer()->get(NotifierInterface::class)
                )
            )->__invoke(
                (new EnvelopeMessages\VoidEnvelopeMessage(
                    $envelopeId,
                    $senderId,
                    $request->request->get('reason')
                ))->withAccessRulesList(['monitor_documents'])
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_void_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Void

        jsonResponse(translate('order_documents_void_action_message_success_text', null, true), 'success', [
            'envelope' => [
                'id' => $envelopeId,
            ],
        ]);
    }

    /**
     * Downloads the original envelope document.
     */
    protected function downloadDocuments(int $userId, ?int $envelopeId, ?int $documentId, bool $authorizeUser = true): void
    {
        //region Download
        try {
            // Propagate the envelope routing
            $accessToken = (
                new EnvelopeCommands\AccessDocument(
                    model(Envelope_Documents_Model::class),
                    new FileStorage(
                        $this->getDocumentsApiClient(),
                        config('env.EP_DOCS_REFERRER', 'http://localhost'),
                        config('env.EP_DOCS_ADMIN_SALT'),
                        library(TinyMVC_Library_Fastcache::class)->pool('epdocs')
                    ),
                    $authorizeUser
                )
            )->__invoke(
                new EnvelopeMessages\AccessDocumentMessage(
                    $envelopeId,
                    $userId,
                    $documentId
                )
            );
        } catch (EnvelopeException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_download_action_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (EPDocsException $e) {
            jsonResponse(
                throwableToMessage($e, translate('order_documents_epdocs_message_generic_error_text', null, true)),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
        //endregion Download

        jsonResponse(null, 'success', [
            'file' => [
                'url'  => config('env.EP_DOCS_HOST', 'http://localhost') . $accessToken['path'],
                'name' => sprintf(
                    '%s_document_%s.%s',
                    orderNumber($envelopeId),
                    $accessToken['slug'],
                    $accessToken['extension'],
                ),
            ],
        ]);
    }

    /**
     * Lists envelopes.
     */
    protected function listEnvelopes(Request $request, int $userId, int $offset, int $perPage, bool $isLegacyMode = false): void
    {
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        list(
            'all'   => $allEnvelopes,
            'total' => $totalEnvelopes,
            'data'  => $envelopesList
        ) = $envelopes->paginateForGrid(
            ['for_orders' => true, 'for_user' => $userId, 'not_status' => EnvelopeStatuses::VOIDED],
            \dtConditions($request->request->get('filters') ?? [], [
                ['as' => 'search',            'key' => 'keywords',     'type' => 'cut_str:200'],
                ['as' => 'id',                'key' => 'document',     'type' => 'toId|intval:10'],
                ['as' => 'order',             'key' => 'order',        'type' => 'toId|intval:10'],
                ['as' => 'created_from_date', 'key' => 'created_from', 'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'created_to_date',   'key' => 'created_to',   'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'updated_from_date', 'key' => 'updated_from', 'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'updated_to_date',   'key' => 'updated_to',   'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
            ]),
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'createdAt' => 'created_at_date',
                        'updatedAt' => 'updated_at_date',
                    ],
                    null,
                    $isLegacyMode
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $envelopesList = $envelopesList ?? [];

        //region Templates
        $templates = $this->getEnvelopeListTemplates();
        list('badge' => $badgeTemplate, 'shallow_action_button' => $shallowActionButtonTemplate) = $templates;
        //endregion Templates

        //region Common elements
        $commonBadges = new DatatablElements\ElementList([
            new DatatableBadges\DraftBadge($badgeTemplate, translate('order_documents_dashboard_badge_draft_text', null, true), 'bg-gray'),
            new DatatableBadges\SignableBadge($badgeTemplate, translate('order_documents_dashboard_badge_signable_text', null, true), 'bg-blue2'),
            new DatatableBadges\InQueueBadge($badgeTemplate, translate('order_documents_dashboard_badge_in_queue_text', null, true), 'bg-orange'),
            new DatatableBadges\AllSignedBadge($badgeTemplate, translate('order_documents_dashboard_badge_all_signed_text', null, true), 'bg-green'),
            new DatatableBadges\CompletedBadge($badgeTemplate, translate('order_documents_dashboard_badge_completed_text', null, true), 'bg-green'),
            new DatatableBadges\NeedToSignBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_to_sign_text', null, true), 'bg-orange'),
            new DatatableBadges\NeedToViewBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_to_view_text', null, true), 'bg-orange'),
            new DatatableBadges\WaitingBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_waiting_for_others_text', null, true), 'bg-orange'),
            new DatatableBadges\SignedBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_signed_text', null, true), 'bg-green'),
            new DatatableBadges\ViewedBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_viewed_text', null, true), 'bg-green'),
            new DatatableBadges\DeclinedBadge($badgeTemplate, translate('order_documents_dashboard_badge_declined_text', null, true), 'bg-red'),
            new DatatableBadges\NeedConfirmation($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_confirmation_text', null, true), 'bg-orange'),
        ]);
        $commonButtons = new ArrayCollection([
            'all' => new ActionButton(
                $shallowActionButtonTemplate,
                translate('general_dt_info_all_text', null, true),
                null,
                'd-none d-md-block d-lg-block d-xl-none',
                null,
                ['callback' => 'showEnvelopeGridRowContent', 'action' => 'documents:envelope-grid:show-row-content']
            ),
        ]);
        //endregion Common elements

        $envelopesList = (new ArrayCollection($envelopesList))->map(
            fn ($envelope) => $this->renderEnvelopeListEntry($userId, $envelope, $commonButtons, $commonBadges, $templates)
        );

        jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $request->request->getInt('draw', 0),
                    'aaData'               => $envelopesList ? $envelopesList->toArray() : [],
                    'iTotalRecords'        => $allEnvelopes ?? 0,
                    'iTotalDisplayRecords' => $totalEnvelopes ?? 0,
                ]
                : [
                    'draw'            => $request->request->getInt('draw', 0),
                    'data'            => $envelopesList ? $envelopesList->toArray() : [],
                    'recordsTotal'    => $allEnvelopes ?? 0,
                    'recordsFiltered' => $totalEnvelopes ?? 0,
                ]
        );
    }

    /**
     * Lists envelopes.
     */
    protected function listAdminEnvelopes(Request $request, int $userId, int $offset, int $perPage, bool $isLegacyMode = false): void
    {
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        /** @var Envelope_Recipients_Model $recipients */
        $recipients = model(Envelope_Recipients_Model::class);

        list(
            'all'   => $allEnvelopes,
            'total' => $totalEnvelopes,
            'data'  => $envelopesList
        ) = $envelopes->paginateForAdminGrid(
            ['for_orders' => true],
            \dtConditions($request->request->get('filters') ?? [], [
                ['as' => 'id',                'key' => 'document',     'type' => 'toId|intval:10'],
                ['as' => 'order',             'key' => 'order',        'type' => 'toId|intval:10'],
                ['as' => 'status',            'key' => 'status',       'type' => 'cleanInput'],
                ['as' => 'type',              'key' => 'type',         'type' => 'cleanInput'],
                ['as' => 'created_from_date', 'key' => 'created_from', 'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'created_to_date',   'key' => 'created_to',   'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'updated_from_date', 'key' => 'updated_from', 'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'updated_to_date',   'key' => 'updated_to',   'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                [
                    'nullable'       => true,
                    'as'             => 'combined_sender',
                    'type'           => function () use ($request) {
                        return fn () => array_values(
                            \dtConditions(
                                $request->request->get('filters') ?? [],
                                [
                                    ['as' => 'user',   'key' => 'sender',      'nullable' => true, 'type' => 'toId|intval:10'],
                                    ['as' => 'name',   'key' => 'sender_name', 'nullable' => true, 'type' => 'cut_str:200|trim'],
                                ]
                            )
                        );
                    },
                ],
                [
                    'nullable'       => true,
                    'as'             => 'combined_recipient',
                    'type'           => function () use ($request) {
                        return fn () => array_values(
                            \dtConditions(
                                $request->request->get('filters') ?? [],
                                [
                                    ['as' => 'user',   'key' => 'recipient',        'nullable' => true, 'type' => 'toId|intval:10'],
                                    ['as' => 'name',   'key' => 'recipient_name',   'nullable' => true, 'type' => 'cut_str:200|trim'],
                                    ['as' => 'type',   'key' => 'recipient_type',   'nullable' => true, 'type' => 'cut_str:200|cleaninput|trim'],
                                    ['as' => 'status', 'key' => 'recipient_status', 'nullable' => true, 'type' => 'cut_str:200|cleaninput|trim'],
                                ]
                            )
                        );
                    },
                ],
                ['as' => 'search',            'key' => 'search',       'type' => 'cut_str:200|trim'],
            ]),
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'type'      => 'type',
                        'status'    => 'status',
                        'createdAt' => 'created_at_date',
                        'updatedAt' => 'updated_at_date',
                    ],
                    null,
                    $isLegacyMode
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $envelopesList = $envelopesList ?? [];

        $recipientsList = [];
        if (!empty($envelopesList)) {
            $recipientsList = arrayByKey(
                $recipients->findAllBy([
                    'with'       => ['user'],
                    'conditions' => ['envelopes' => array_column($envelopesList, 'id')],
                ]),
                'id_envelope',
                true
            );
        }

        //region Templates
        $templates = $this->getEnvelopeAdminListTemplates();
        //endregion Templates

        $envelopesList = (new ArrayCollection($envelopesList))->map(
            fn (array $envelope) => $this->renderEnvelopeAdminListEntry($envelope, $templates, $recipientsList)
        );

        jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $request->request->getInt('draw', 0),
                    'aaData'               => $envelopesList ? $envelopesList->toArray() : [],
                    'iTotalRecords'        => $allEnvelopes ?? 0,
                    'iTotalDisplayRecords' => $totalEnvelopes ?? 0,
                ]
                : [
                    'draw'            => $request->request->getInt('draw', 0),
                    'data'            => $envelopesList ? $envelopesList->toArray() : [],
                    'recordsTotal'    => $allEnvelopes ?? 0,
                    'recordsFiltered' => $totalEnvelopes ?? 0,
                ]
        );
    }

    /**
     * Lists envelopes.
     */
    protected function listDetachedEnvelopes(Request $request, int $userId, int $offset, int $perPage, bool $isLegacyMode = false): void
    {
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        list(
            'all'   => $allEnvelopes,
            'total' => $totalEnvelopes,
            'data'  => $envelopesList
        ) = $envelopes->paginateForGrid(
            array_merge(
                ['for_orders' => true, 'for_user' => $userId, 'not_status' => EnvelopeStatuses::VOIDED],
                \dtConditions($request->request->get('filters') ?? [], [
                    ['as' => 'order', 'key' => 'order', 'type' => 'toId|intval:10'],
                ])
            ),
            [],
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'createdAt' => 'created_at_date',
                        'updatedAt' => 'updated_at_date',
                    ],
                    null,
                    $isLegacyMode
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $envelopesList = $envelopesList ?? [];

        //region Templates
        $templates = $this->getEnvelopeListTemplates();
        list('badge' => $badgeTemplate, 'shallow_action_button' => $shallowActionButtonTemplate) = $templates;
        //endregion Templates

        //region Common elements
        $commonBadges = new DatatablElements\ElementList([
            new DatatableBadges\DraftBadge($badgeTemplate, translate('order_documents_dashboard_badge_draft_text', null, true), 'bg-gray'),
            new DatatableBadges\SignableBadge($badgeTemplate, translate('order_documents_dashboard_badge_signable_text', null, true), 'bg-blue2'),
            new DatatableBadges\InQueueBadge($badgeTemplate, translate('order_documents_dashboard_badge_in_queue_text', null, true), 'bg-orange'),
            new DatatableBadges\AllSignedBadge($badgeTemplate, translate('order_documents_dashboard_badge_all_signed_text', null, true), 'bg-green'),
            new DatatableBadges\CompletedBadge($badgeTemplate, translate('order_documents_dashboard_badge_completed_text', null, true), 'bg-green'),
            new DatatableBadges\NeedToSignBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_to_sign_text', null, true), 'bg-orange'),
            new DatatableBadges\NeedToViewBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_to_view_text', null, true), 'bg-orange'),
            new DatatableBadges\WaitingBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_waiting_for_others_text', null, true), 'bg-orange'),
            new DatatableBadges\SignedBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_signed_text', null, true), 'bg-green'),
            new DatatableBadges\ViewedBadge($userId, $badgeTemplate, translate('order_documents_dashboard_badge_viewed_text', null, true), 'bg-green'),
            new DatatableBadges\DeclinedBadge($badgeTemplate, translate('order_documents_dashboard_badge_declined_text', null, true), 'bg-red'),
            new DatatableBadges\NeedConfirmation($userId, $badgeTemplate, translate('order_documents_dashboard_badge_need_confirmation_text', null, true), 'bg-orange'),
        ]);
        $commonButtons = new ArrayCollection([
            'all' => new ActionButton(
                $shallowActionButtonTemplate,
                translate('general_dt_info_all_text', null, true),
                null,
                'd-none d-md-block d-lg-block d-xl-none',
                null,
                ['callback' => 'showEnvelopeGridRowContent', 'action' => 'documents:envelope-grid:show-row-content']
            ),
        ]);
        //endregion Common elements

        $envelopesList = (new ArrayCollection($envelopesList))->map(
            fn ($envelope) => $this->renderEnvelopeListEntry($userId, $envelope, $commonButtons, $commonBadges, $templates)
        );

        jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $request->request->getInt('draw', 0),
                    'aaData'               => $envelopesList ? $envelopesList->toArray() : [],
                    'iTotalRecords'        => $allEnvelopes ?? 0,
                    'iTotalDisplayRecords' => $totalEnvelopes ?? 0,
                ]
                : [
                    'draw'            => $request->request->getInt('draw', 0),
                    'data'            => $envelopesList ? $envelopesList->toArray() : [],
                    'recordsTotal'    => $allEnvelopes ?? 0,
                    'recordsFiltered' => $totalEnvelopes ?? 0,
                ]
        );
    }

    /**
     * Lists envelopes.
     */
    protected function listAdminDetachedEnvelopes(Request $request, int $userId, int $offset, int $perPage, bool $isLegacyMode = false): void
    {
        /** @var Envelopes_Model $envelopes */
        $envelopes = model(Envelopes_Model::class);
        /** @var Envelope_Recipients_Model $recipients */
        $recipients = model(Envelope_Recipients_Model::class);

        list(
            'all'   => $allEnvelopes,
            'total' => $totalEnvelopes,
            'data'  => $envelopesList
        ) = $envelopes->paginateForAdminGrid(
            ['for_orders' => true],
            \dtConditions($request->request->get('filters') ?? [], [
                ['as' => 'order', 'key' => 'order', 'type' => 'toId|intval:10'],
            ]),
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'type'      => 'type',
                        'status'    => 'status',
                        'createdAt' => 'created_at_date',
                        'updatedAt' => 'updated_at_date',
                    ],
                    null,
                    $isLegacyMode
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $envelopesList = $envelopesList ?? [];

        $recipientsList = [];
        if (!empty($envelopesList)) {
            $recipientsList = arrayByKey(
                $recipients->findAllBy([
                    'with'       => ['user'],
                    'conditions' => ['envelopes' => array_column($envelopesList, 'id')],
                ]),
                'id_envelope',
                true
            );
        }

        //region Templates
        $templates = $this->getEnvelopeAdminListTemplates();
        //endregion Templates

        $envelopesList = (new ArrayCollection($envelopesList))->map(
            fn (array $envelope) => $this->renderEnvelopeAdminListEntry($envelope, $templates, $recipientsList, true)
        );

        jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $request->request->getInt('draw', 0),
                    'aaData'               => $envelopesList ? $envelopesList->toArray() : [],
                    'iTotalRecords'        => $allEnvelopes ?? 0,
                    'iTotalDisplayRecords' => $totalEnvelopes ?? 0,
                ]
                : [
                    'draw'            => $request->request->getInt('draw', 0),
                    'data'            => $envelopesList ? $envelopesList->toArray() : [],
                    'recordsTotal'    => $allEnvelopes ?? 0,
                    'recordsFiltered' => $totalEnvelopes ?? 0,
                ]
        );
    }

    /**
     * Returns the optins for documents download.
     */
    private function getUploadOptions(int $currentAmount = 0): array
    {
        return $this->getFormattedFileuploadOptions(
            explode(',', config('fileupload_orders_document_formats', 'pdf,jpg,jpeg,png')),
            1,
            max(0, 1 - $currentAmount),
            (int) config('fileupload_max_document_file_size', 2 * 1000 * 1000),
            config('fileupload_max_document_file_size_placeh', '2MB')
        );
    }

    /**
     * Returns the EPDocs API client.
     */
    private function getDocumentsApiClient(): RestClient
    {
        $configs = (new \App\Plugins\EPDocs\Configuration())->setHttpOrigin(config('env.EP_DOCS_REFERRER'))->setDefaultUserId(config('env.EP_DOCS_ADMIN_SALT'));
        $client = new Client(['base_uri' => config('env.EP_DOCS_HOST', 'http://localhost')]);
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
     * Returns normalized assignees for order.
     */
    private function getNormalizedOrderAssignees(array $order): array
    {
        // Normalize shipper
        if ('ishipper' === $order['shipper_type'] ?? null) {
            unset($order['shipper']);
        }

        //region Assignees
        return arrayByKey(
            array_map(
                fn (string $type, array $user): array => [
                    'id'    => (int) ($user['user'] ?? $user['id']),
                    'name'  => $userName = $user['company_name'] ?? $user['fullname'] ?? 'Unknown user',
                    'type'  => $type,
                    'group' => $user['group_name'],
                    'color' => userGroupNameColor($user['group_name']),
                    'title' => sprintf('%s (%s)', $userName, $user['group_name']),
                    'text'  => translate(
                        // I wish I could have `match()` expression.
                        arrayGet(
                            [
                                'buyer'   => 'order_documents_dashboard_edit_popup_assignee_type_buyer_text',
                                'seller'  => 'order_documents_dashboard_edit_popup_assignee_type_seller_text',
                                'shipper' => 'order_documents_dashboard_edit_popup_assignee_type_shipper_text',
                            ],
                            $type
                        ),
                        ['[NAME]' => $userName]
                    ),
                ],
                array_keys(
                    $assignees = array_filter([
                        'buyer'   => $order['buyer'] ?? null,
                        'seller'  => $order['seller'] ?? null,
                        'shipper' => $order['shipper'] ?? null,
                    ])
                ),
                $assignees
            ),
            'id'
        );
        //endregion Assignees
    }

    /**
     * Prepares the recipients for details preview.
     */
    private function prepareRecipientForDetailsPreview(Collection $recipients, array $assignees): Collection
    {
        return $this->prepareRecipientForEditPreview($recipients, $assignees)->map(fn (array $recipient) => array_merge($recipient, [
            'display_type'    => ucfirst($recipient['recipient_type']),
            'expiration_date' => null !== $recipient['expires_at']
                ? getDateFormatIfNotEmpty($recipient['expires_at'], 'm/d/Y', \App\Common\PUBLIC_DATE_FORMAT)
                : null,
            // I wish I could have `match()` expression even more now.
            'status_badge'   => arrayGet(
                [
                    RecipientStatuses::CREATED   => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_created_text', null, true),
                        'color' => 'badge-secondary',
                    ],
                    RecipientStatuses::SENT      => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_sent_text', null, true),
                        'color' => 'badge-primary',
                    ],
                    RecipientStatuses::DELIVERED => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_delivered_text', null, true),
                        'color' => 'badge-primary',
                    ],
                    RecipientStatuses::COMPLETED => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_completed_text', null, true),
                        'color' => 'badge-success',
                    ],
                    RecipientStatuses::SIGNED    => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_signed_text', null, true),
                        'color' => 'badge-success',
                    ],
                    RecipientStatuses::DECLINED  => [
                        'text'  => translate('order_documents_dashboard_details_popup_recipients_badge_declined_text', null, true),
                        'color' => 'badge-danger',
                    ],
                ],
                $recipient['status']
            ),
        ]));
    }

    /**
     * Prepares the recipients for edit preview.
     */
    private function prepareRecipientForEditPreview(Collection $recipients, array $assignees): Collection
    {
        return $recipients->map(fn (array $recipient) => [
            'type'                 => $recipient['type'],
            'status'               => $recipient['status'],
            'assignee'             => $assignees[$recipient['id_user']]['id'],
            'expires_at'           => null !== $recipient['due_date'] ? $recipient['due_date']->format(\App\Common\PUBLIC_DATE_FORMAT) : null,
            'assignee_name'        => cleanOutput($assignees[$recipient['id_user']]['name']),
            'assignee_group'       => cleanOutput($assignees[$recipient['id_user']]['group']),
            'assignee_group_color' => cleanOutput($assignees[$recipient['id_user']]['color']),
            'assignee_title'       => cleanOutput($assignees[$recipient['id_user']]['text']),
            'recipient_type'       => translate(
                // I wish I could have `match()` expression.
                arrayGet(
                    [
                        RecipientTypes::SIGNER => 'order_documents_recipient_types_signer_list_option_text',
                        RecipientTypes::VIEWER => 'order_documents_recipient_types_viewer_list_option_text',
                    ],
                    $recipient['type']
                ),
                null,
                true
            ),
        ]);
    }

    /**
     * Prepares the documents for edit preview.
     */
    private function prepareDocumentsForEditPreview(Collection $files): Collection
    {
        return $files->map(fn (array $file) => [
            'id'        => $file['id'],
            'input'     => 'old-files[]',
            'extension' => $file['file_extension'],
        ]);
    }

    /**
     * Renders one entry for the envelope list grid.
     *
     * @param array<string, TemplateInterface> $templates
     */
    private function renderEnvelopeListEntry(
        int $userId,
        array $envelope,
        Collection $commonButtons,
        ElementListInterface $commonBadges,
        array $templates
    ): array {
        $orderId = (int) $envelope['order_reference']['id_order'];
        $envelopeId = (int) $envelope['id'];

        //region Templates
        list(
            'action_button'   => $actionButtonTemplate,
            'confirm_button'  => $confirmButtonTemplate,
            'document_button' => $documentActionButtonTemplate,
            'popup_button'    => $popupButtonTemplate,
            'description'     => $descriptionTemplate,
            'details'         => $detailsTemplate,
            'actions'         => $actionsTemplate) = $templates;
        //endregion Templates

        //region Badges

        $date = null;
        if (isset($envelope['recipients_routing']['current_routing'][$userId]['due_date'])) {
            $date = $envelope['recipients_routing']['current_routing'][$userId]['due_date']->format(\App\Common\PUBLIC_DATE_FORMAT);
        }

        $envelopeBadges = $commonBadges->merge(
            new DatatablElements\ElementList([
                new DatatableBadges\DueDateBadge($userId, $templates['badge'], translate('order_documents_dashboard_due_date_text', ['[DUE_DATE]' => $date], true), 'bg-red'),
            ])
        );
        //endregion Badges

        //region Details
        $orderNumber = orderNumber($orderId);
        $envelopePreview = new DatatablElements\Element(
            $detailsTemplate->withAttributes([
                'badges' => $envelopeBadges->acceptsRow($envelope),
                'type'   => cleanOutput($envelope['display_type']),
                'title'  => cleanOutput($envelope['display_title']),
                'label'  => translate('order_documents_dashboard_datagrid_column_document_label_title', ['[[DOCUMENT]]' => orderNumber($envelopeId)], true),
                'popup'  => [
                    'url'    => getUrlForGroup("order/popups_order/order_detail/{$orderId}"),
                    'title'  => translate('order_documents_dashboard_datagrid_column_document_order_modal_title', ['[[ORDER]]' => $orderNumber], true),
                    'button' => [
                        'title' => translate('order_documents_dashboard_datagrid_column_document_order_label_title', ['[[ORDER]]' => $orderNumber], true),
                        'text'  => translate('order_documents_dashboard_datagrid_column_document_order_label_text', ['[[ORDER]]' => $orderNumber], true),
                    ],
                ],
            ])
        );
        //endregion Details

        //region Description
        $descriptionPreview = '&mdash;';
        if (!empty($envelope['display_description'])) {
            $descriptionPreview = new DatatablElements\Element(
                $descriptionTemplate->withAttributes(['text' => cleanOutput(strLimit($envelope['display_description'], 500))])
            );
        }
        //endregion Description

        //region Actions
        $envelopeButtons = new DatatablElements\ElementList([
            new DatatableButtons\EditPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/edit-envelope/{$envelopeId}"),
                translate('general_button_edit_text', null, true),
                translate('order_documents_dashboard_button_edit_document_title', null, true),
                null,
                null,
                'ep-icon ep-icon_pencil'
            ),
            new DatatableButtons\EditDescriptionPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/update-envelope-info/{$envelopeId}"),
                translate('general_button_edit_text', null, true),
                translate('order_documents_dashboard_button_edit_document_title', null, true),
                null,
                null,
                'ep-icon ep-icon_pencil'
            ),
            new DatatableButtons\SendActionButton(
                $userId,
                $confirmButtonTemplate,
                translate('order_documents_dashboard_button_send_info_text', null, true),
                translate('order_documents_dashboard_button_send_info_title', null, true),
                null,
                'ep-icon ep-icon_reply-right-empty',
                [
                    'action'   => 'documents:envelope-grid:send-envelope',
                    'message'  => translate('order_documents_dashboard_button_send_info_message', null, true),
                    'callback' => 'sendEnvelopeToRecipients',
                    'envelope' => $envelopeId,
                ]
            ),
            new DatatableButtons\RequireApprovalButton(
                $userId,
                $confirmButtonTemplate,
                translate('order_documents_dashboard_button_send_info_text', null, true),
                translate('order_documents_dashboard_button_send_info_title', null, true),
                null,
                'ep-icon ep-icon_reply-right-empty',
                [
                    'action'   => 'documents:envelope-grid:require-envelope-approval',
                    'message'  => translate('order_documents_dashboard_button_send_info_message', null, true),
                    'callback' => 'requireEnvelopeApproval',
                    'envelope' => $envelopeId,
                ]
            ),
            new DatatableButtons\UploadSignedPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/sign-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_upload_signed_text', null, true),
                translate('order_documents_dashboard_button_upload_signed_title', null, true),
                null,
                null,
                'ep-icon ep-icon_upload'
            ),
            new DatatableButtons\SignActionButton(
                $userId,
                $actionButtonTemplate,
                translate('order_documents_dashboard_button_sign_text', null, true),
                translate('order_documents_dashboard_button_sign_title', null, true),
                null,
                'ep-icon ep-icon_pencil-sign',
                [
                    'action'   => 'documents:envelope-grid:access-remote-envelope',
                    'callback' => 'accessRemoteEnvelope',
                    'envelope' => $envelopeId,
                ]
            ),
            new DatatableButtons\ConfirmSignedActionButton(
                $userId,
                $confirmButtonTemplate,
                translate('order_documents_dashboard_button_confirm_text', null, true),
                translate('order_documents_dashboard_button_confirm_title', null, true),
                null,
                'ep-icon ep-icon_ok-stroke',
                [
                    'action'   => 'documents:envelope-grid:confirm-envelope',
                    'message'  => translate('order_documents_dashboard_button_confirm_message', null, true),
                    'callback' => 'confirmSignedEnvelope',
                    'envelope' => $envelopeId,
                ]
            ),
            new DatatableButtons\DeclineSignedPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/decline-signed-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_decline_signed_text', null, true),
                translate('order_documents_dashboard_button_decline_signed_title', null, true),
                null,
                null,
                'ep-icon ep-icon_remove-stroke'
            ),
            new DatatableButtons\DeclinePopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/decline-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_decline_text', null, true),
                translate('order_documents_dashboard_button_decline_title', null, true),
                null,
                null,
                'ep-icon ep-icon_remove-stroke'
            ),
            new DatatableButtons\ViewDocumentActionButton(
                $userId,
                $documentActionButtonTemplate,
                translate('order_documents_dashboard_button_view_text', null, true),
                translate('order_documents_dashboard_button_view_title', null, true),
                null,
                'ep-icon ep-icon_visible',
                [
                    'action'   => 'documents:envelope-grid:view-envelope',
                    'callback' => 'viewEnvelopeAsRecipient',
                    'envelope' => $envelopeId,
                    'document' => $envelope['documents']['latest']['id'] ?? $envelope['documents']['original']['id'] ?? null,
                ]
            ),
            new DatatableButtons\ViewActionButton(
                $userId,
                $actionButtonTemplate,
                translate('order_documents_dashboard_button_view_text', null, true),
                translate('order_documents_dashboard_button_view_title', null, true),
                null,
                'ep-icon ep-icon_visible',
                [
                    'action'   => 'documents:envelope-grid:access-remote-envelope',
                    'callback' => 'accessRemoteEnvelope',
                    'envelope' => $envelopeId,
                ]
            ),
            new DatatableButtons\CopyOriginalPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/copy-original-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_copy_text', null, true),
                translate('order_documents_dashboard_button_copy_modal_title', null, true),
                translate('order_documents_dashboard_button_copy_title', null, true),
                null,
                'ep-icon ep-icon_file-copy'
            ),
            new DatatableButtons\ForwardCopyPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/forward-latest-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_forward_text', null, true),
                translate('order_documents_dashboard_button_forward_modal_title', null, true),
                translate('order_documents_dashboard_button_forward_title', null, true),
                null,
                'ep-icon ep-icon_file-copy'
            ),
            new DatatableButtons\ViewDetailsPopupButton(
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/view-envelope-details/{$envelopeId}"),
                translate('order_documents_dashboard_button_details_text', null, true),
                translate('order_documents_dashboard_button_details_title', null, true),
                null,
                null,
                'ep-icon ep-icon_file-text'
            ),
            // new DatatableButtons\ViewHistoryPopupButton(
            //     $popupButtonTemplate,
            //     getUrlForGroup("order_documents/popup_forms/view-envelope-history/{$envelopeId}"),
            //     translate('order_documents_dashboard_button_history_text', null, true),
            //     translate('order_documents_dashboard_button_history_title', null, true),
            //     null,
            //     null,
            //     'ep-icon ep-icon_hourglass-timeout'
            // ),
            new DatatableDecorate\SenderOrRecipientPassDecorator(
                new DatatableButtons\DownloadOriginalActionButton(
                    $documentActionButtonTemplate,
                    translate('order_documents_dashboard_button_download_original_text', null, true),
                    translate('order_documents_dashboard_button_download_original_title', null, true),
                    null,
                    'ep-icon ep-icon_download-stroke',
                    [
                        'action'   => 'documents:envelope-grid:download-document',
                        'callback' => 'downloadEnvelopeDocument',
                        'envelope' => $envelopeId,
                        'document' => $envelope['documents']['original']['id'] ?? null,
                    ],
                    false
                ),
                $userId
            ),
            new DatatableDecorate\SenderOrRecipientPassDecorator(
                new DatatableButtons\DownloadLatestActionButton(
                    $documentActionButtonTemplate,
                    translate('order_documents_dashboard_button_download_latest_text', null, true),
                    translate('order_documents_dashboard_button_download_latest_title', null, true),
                    null,
                    'ep-icon ep-icon_download-stroke',
                    [
                        'action'   => 'documents:envelope-grid:download-document',
                        'callback' => 'downloadEnvelopeDocument',
                        'envelope' => $envelopeId,
                        'document' => $envelope['documents']['latest']['id'] ?? null,
                    ],
                    false
                ),
                $userId
            ),
            new DatatableButtons\VoidPopupButton(
                $userId,
                $popupButtonTemplate,
                getUrlForGroup("order_documents/popup_forms/void-envelope/{$envelopeId}"),
                translate('order_documents_dashboard_button_delete_text', null, true),
                translate('order_documents_dashboard_button_delete_title', null, true),
                null,
                null,
                'ep-icon ep-icon_trash-stroke',
            ),
            $commonButtons->get('all'),
        ]);
        //endregion Actions

        return [
            'preview'     => $envelopePreview,
            'description' => $descriptionPreview,
            'createdAt'   => getDateFormatIfNotEmpty(arrayGet($envelope, 'created_at_date')),
            'updatedAt'   => getDateFormatIfNotEmpty(arrayGet($envelope, 'updated_at_date')),
            'actions'     => new DatatablElements\Element($actionsTemplate->withAttributes(['buttons' => $envelopeButtons->acceptsRow($envelope)])),
        ];
    }

    /**
     * Gets the set of templates for envelope list entries.
     */
    private function getEnvelopeListTemplates(): array
    {
        //region Templates
        $badgeTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <div class="main-data-table__item-action {{color}}"><div class="text">{{text}}</div></div>
            TEMPLATE
        );
        $confirmButtonTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <a class="dropdown-item confirm-dialog {{class}}"
                data-message="{{data.message}}"
                data-callback="{{data.callback}}"
                data-js-action="{{data.action}}"
                data-envelope="{{data.envelope}}"
                title="{{title}}">
                <i class="{{icon}}"></i>
                <span>{{text}}</span>
            </a>
            TEMPLATE
        );
        $actionButtonTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <a class="dropdown-item call-action call-function {{class}}"
                data-callback="{{data.callback}}"
                data-js-action="{{data.action}}"
                data-envelope="{{data.envelope}}"
                title="{{title}}">
                <i class="{{icon}}"></i>
                <span>{{text}}</span>
            </a>
            TEMPLATE
        );
        $documentActionButtonTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <a class="dropdown-item call-action call-function {{class}}"
                data-callback="{{data.callback}}"
                data-js-action="{{data.action}}"
                data-envelope="{{data.envelope}}"
                data-document="{{data.document}}"
                title="{{title}}">
                <i class="{{icon}}"></i>
                <span>{{text}}</span>
            </a>
            TEMPLATE
        );
        $shallowActionButtonTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <a class="dropdown-item {{class}} call-action call-function"
                data-callback="{{data.callback}}"
                data-js-action="{{data.action}}"
                target="_blank">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <span>{{text}}</span>
            </a>
            TEMPLATE
        );
        $popupButtonTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <a class="dropdown-item fancybox.ajax fancyboxValidateModal {{class}}"
                data-fancybox-href="{{url}}"
                data-title="{{popupTitle}}"
                title="{{title}}">
                <i class="{{icon}}"></i>
                <span>{{text}}</span>
            </a>
            TEMPLATE
        );
        $descriptionTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <div class="grid-text">
                <div class="grid-text__item">
                    <div>{{text}}</div>
                </div>
            </div>
            TEMPLATE
        );
        $detailsTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <div class="relative-b">
                <div class="main-data-table__item-actions">{{badges}}</div>
                <div class="ml-25">
                    <div class="main-data-table__item-ttl mw-300">
                        <div class="txt-medium text-nowrap">{{label}}</div>
                    </div>
                    <div class="main-data-table__item-ttl mw-300">
                        <div class="txt-medium text-nowrap" title="{{title}}">{{title}}</div>
                    </div>
                    <div class="main-data-table__item-ttl mw-300">
                        <div class="text-nowrap" title="{{type}}">{{type}}</div>
                    </div>
                    <div class="main-data-table__item-ttl mw-300">
                        <a class="link-black text-nowrap fancybox.ajax fancyboxValidateModal"
                            data-fancybox-href="{{popup.url}}"
                            data-title="{{popup.title}}"
                            target="_blank"
                            title="{{popup.button.title}}">
                            {{popup.button.text}}
                        </a>
                    </div>
                <div>
            </div>
            TEMPLATE
        );
        $actionsTemplate = new TextTemplate(
            <<<'TEMPLATE'
            <div class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ep-icon ep-icon_menu-circles"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">{{buttons}}</div>
            </div>
            TEMPLATE,
        );
        //endregion Templates

        return [
            'badge'                 => $badgeTemplate,
            'action_button'         => $actionButtonTemplate,
            'confirm_button'        => $confirmButtonTemplate,
            'shallow_action_button' => $shallowActionButtonTemplate,
            'document_button'       => $documentActionButtonTemplate,
            'popup_button'          => $popupButtonTemplate,
            'description'           => $descriptionTemplate,
            'details'               => $detailsTemplate,
            'actions'               => $actionsTemplate,
        ];
    }

    /**
     * Renders one entry for the envelope admin list grid.
     *
     * @param array<string, TemplateInterface> $templates
     */
    private function renderEnvelopeAdminListEntry(
        array $envelope,
        array $templates,
        array $recipientsList,
        bool $isCompact = false
    ): array {
        $sender = $envelope['sender'] ?? null;
        $orderId = (int) $envelope['order_reference']['id_order'];
        $senderId = $envelope['id_sender'] ?? null;
        $envelopeId = (int) $envelope['id'];
        $envelopeRecipients = $recipientsList[$envelopeId] ?? [];
        $filtersTemplate = !$isCompact ? ($templates['filter'] ?? '') : new TextTemplate('');

        //region Details
        $envelopeDetails = $envelopeDetails = new DatatablElements\Element(
            $templates['details']->withAttributes([
                'envelope' => cleanOutput(orderNumber($envelopeId)),
            ])
        );
        //endregion Details

        //region Description
        $descriptionPreview = new DatatablElements\Element(
            $templates['description']->withAttributes([
                'text' => cleanOutput($envelope['display_description'] ?? null) ?: '&mdash;',
            ])
        );
        //endregion Description

        //region Type Preview
        $typePreview = new DatatablElements\Element(
            $templates['text_with_filter']->withAttributes([
                'text'   => new DatatablElements\Element(
                    $templates['label']->withAttributes([
                        'text'  => $typeTitle = cleanOutput(ucfirst($envelope['type'])),
                        'type'  => cleanOutput(with($envelope['type'], fn (string $type) => arrayGet(
                            [
                                EnvelopeTypes::PERSONAL => 'primary',
                                EnvelopeTypes::INTERNAL => 'warning',
                            ],
                            $type
                        ))),
                    ])
                ),
                'filter' => new DatatablElements\Element(
                    $filtersTemplate->withAttributes([
                        'value'  => ['real' => $envelope['type'], 'display' => $typeTitle],
                        'button' => ['title' => 'Filter by type'],
                        'title'  => 'Type',
                        'name'   => 'type',
                    ])
                ),
            ])
        );
        //endregion Type Preview

        //region Status Preview
        $statusPreview = new DatatablElements\Element(
            $templates['text_with_filter']->withAttributes([
                'text'   => new DatatablElements\Element(
                    $templates['label']->withAttributes([
                        'text'  => $statusTitle = cleanOutput(with($envelope['status'], fn (string $status) => arrayGet(
                            [
                                EnvelopeStatuses::CREATED       => 'Draft',
                                EnvelopeStatuses::VOIDED        => 'Deleted',
                                EnvelopeStatuses::NOT_PROCESSED => 'Need Processing',
                                EnvelopeStatuses::PROCESSED     => 'Processed',
                            ],
                            $status,
                            ucfirst($status),
                        ))),
                        'type'  => cleanOutput(with($envelope['status'], fn (string $status) => arrayGet(
                            [
                                EnvelopeStatuses::SENT          => 'primary',
                                EnvelopeStatuses::CREATED       => 'default',
                                EnvelopeStatuses::DELIVERED     => 'primary',
                                EnvelopeStatuses::COMPLETED     => 'success',
                                EnvelopeStatuses::DECLINED      => 'danger',
                                EnvelopeStatuses::SIGNED        => 'default',
                                EnvelopeStatuses::VOIDED        => 'danger',
                                EnvelopeStatuses::NOT_PROCESSED => 'warning',
                                EnvelopeStatuses::PROCESSED     => 'primary',
                            ],
                            $status
                        ))),
                    ])
                ),
                'filter' => new DatatablElements\Element(
                    $filtersTemplate->withAttributes([
                        'value'  => ['real' => $envelope['status'], 'display' => $statusTitle],
                        'button' => ['title' => 'Filter by status'],
                        'title'  => 'Status',
                        'name'   => 'status',
                    ])
                ),
            ])
        );
        //endregion Status Preview

        //region Order Preview
        $orderPreview = new DatatablElements\Element(
            $templates['order']->withAttributes([
                'url'    => getUrlForGroup("/order/popups_order/order_detail/{$orderId}"),
                'number' => $orderNumber = cleanOutput(orderNumber($orderId)),
                'filter' => new DatatablElements\Element(
                    $filtersTemplate->withAttributes([
                        'value'  => ['real' => $orderId, 'display' => $orderNumber],
                        'button' => ['title' => 'Filter by order'],
                        'title'  => 'Order',
                        'name'   => 'order',
                    ])
                ),
            ])
        );
        //endregion Order Preview

        //region Sender Preview
        $senderPreview = '&mdash;';
        if (null !== $sender) {
            $legalName = empty($sender['legal_name']) ? '&mdash;' : $templates['label']->withAttributes([
                'type' => 'default',
                'text' => cleanOutput($sender['legal_name']),
            ]);

            $senderPreview = new DatatablElements\Element(
                $templates['sender']->withAttributes([
                    'name'   => ['current' => $senderName = cleanOutput($sender['name']), 'legal' => $legalName],
                    'page'   => new DatatablElements\Element(
                        $templates['link_with_icon']->withAttributes([
                            'url'   => getUserLink($sender['name'], $sender['id'], $sender['group']['type']),
                            'class' => 'ep-icon ep-icon_user',
                            'title' => sprintf('View personal name of %s', $senderName),
                        ])
                    ),
                    'filter' => new DatatablElements\Element(
                        $filtersTemplate->withAttributes([
                            'value'  => ['real' => $senderId, 'display' => cleanOutput(orderNumber($senderId))],
                            'button' => ['title' => 'Filter by sender'],
                            'title'  => 'Sender',
                            'name'   => 'sender',
                        ])
                    ),
                ])
            );
        }
        //endregion Sender Preview

        //region Envelope Preview
        $envelopePreview = new DatatablElements\Element(
            $templates['envelope']->withAttributes([
                'type'   => cleanOutput($envelope['display_type']),
                'title'  => cleanOutput($envelope['display_title']),
            ])
        );
        //endregion Envelope Preview

        //region Recipients
        $recipientsList = [];
        usort($envelopeRecipients, fn (array $current, array $next) => $current['routing_order'] <=> $next['routing_order']);
        foreach ($envelopeRecipients as $recipient) {
            $recipientUserId = $recipient['id_user'];
            $recipientBadges = new DatatablElements\ElementList([
                new DatatableBadges\NeedToSignBadge($recipientUserId, $templates['badge'], 'Need to Sign', 'warning'),
                new DatatableBadges\NeedToViewBadge($recipientUserId, $templates['badge'], 'Need to View', 'warning'),
                new DatatableBadges\WaitingBadge($recipientUserId, $templates['badge'], 'Waiting for Others', 'warning'),
                new DatatableBadges\SignedBadge($recipientUserId, $templates['badge'], 'Signed', 'success'),
                new DatatableBadges\ViewedBadge($recipientUserId, $templates['badge'], 'Viewed', 'success'),
                new DatatableBadges\NeedConfirmation($recipientUserId, $templates['badge'], 'Need Confirmation', 'warning'),
            ]);

            $recipientFilter = $filtersTemplate->withAttributes([
                'value'  => ['real' => $recipientUserId, 'display' => cleanOutput(orderNumber($recipientUserId))],
                'button' => ['title' => 'Filter by recipient'],
                'title'  => 'Recipient',
                'name'   => 'recipient',
            ]);
            $recipientStatus = $templates['label']->withAttributes([
                'text'  => cleanOutput(ucfirst($recipient['status'])),
                'type'  => cleanOutput(with($recipient['status'], fn (string $status) => arrayGet(
                    [
                        RecipientStatuses::CREATED   => 'default',
                        RecipientStatuses::SENT      => 'primary',
                        RecipientStatuses::DELIVERED => 'primary',
                        RecipientStatuses::SIGNED    => 'success',
                        RecipientStatuses::COMPLETED => 'success',
                        RecipientStatuses::DECLINED  => 'danger',
                    ],
                    $status
                ))),
            ]);
            $recipientType = new DatatablElements\Element(
                $templates['label']->withAttributes([
                    'text'  => $statusTitle = cleanOutput(with($recipient['type'], fn (string $type) => arrayGet(
                        [
                            RecipientTypes::OPERATOR => 'Operator',
                            RecipientTypes::SIGNER   => 'Signer',
                            RecipientTypes::VIEWER   => 'Viewer',
                        ],
                        $type,
                        ucfirst($type),
                    ))),
                    'type'  => cleanOutput(with($recipient['type'], fn (string $type) => arrayGet(
                        [
                            RecipientTypes::OPERATOR => 'default',
                            RecipientTypes::SIGNER   => 'warning',
                            RecipientTypes::VIEWER   => 'primary',
                        ],
                        $type,
                        ucfirst($type),
                    ))),
                ])
            );

            $statusAppendix = '';
            if (RecipientStatuses::DECLINED === $recipient['status']) {
                // recipient_declining
                $statusAppendix = new DatatablElements\Element(
                    $templates['recipient_declining']->withAttributes([
                        'type'   => $recipient['declined_by_sender'] ? 'Yes' : 'No',
                        'reason' => cleanOutput($recipient['decline_reason'] ?? null) ?: '&mdash;',
                    ])
                );
            }

            $recipientsList[] = new DatatablElements\Element(
                $templates['recipient_entry']->withAttributes([
                    'name'        => cleanOutput(trim(($recipient['user']['fname'] ?? '') . ' ' . ($recipient['user']['lname'] ?? ''))) ?: '&mdash;',
                    'index'       => $recipient['routing_order'],
                    'type'        => $recipientType,
                    'filter'      => $recipientFilter,
                    'status'      => $recipientStatus,
                    'action'      => $recipientBadges->acceptsRow($envelope)->render() ?: '&mdash;',
                    'sentAt'      => getDateFormatIfNotEmpty($recipient['sent_at_date'] ?? null),
                    'signedAt'    => getDateFormatIfNotEmpty($recipient['signed_at_date'] ?? null),
                    'declinedAt'  => getDateFormatIfNotEmpty($recipient['declined_at_date'] ?? null),
                    'completedAt' => getDateFormatIfNotEmpty($recipient['completed_at_date'] ?? null),
                    'deliveredAt' => getDateFormatIfNotEmpty($recipient['delivery_at_date'] ?? null),
                    'dueDate'     => getDateFormatIfNotEmpty($recipient['due_date'] ?? null, null, 'j M, Y'),
                    'appendix'    => $statusAppendix,
                ])
            );
        }
        //endregion Recipients

        //region Actions

        $envelopeButtons = new DatatablElements\ElementList([
            new DatatableButtons\AddEnvelopeTabsActionButton(
                $templates['action'],
                'Add tabs',
                'Add tabs',
                null,
                'ep-icon ep-icon_file-add',
                [
                    'action'   => 'documents:envelope-grid:add-envelope-tabs',
                    'callback' => 'addEnvelopeTabs',
                    'envelope' => $envelopeId,
                ],
                true
            ),
            new DatatableButtons\EditDueDatePopupButton(
                $templates['popup_button'],
                getUrlForGroup("order_documents/popup_admin_forms/edit-due-dates/{$envelopeId}"),
                translate('order_documents_dashboard_button_popup_edit_due_dates_text', null, true),
                translate('order_documents_dashboard_button_popup_edit_due_dates_text', null, true),
                null,
                null,
                'ep-icon ep-icon_clock-stroke2',
            ),
            new DatatableButtons\DownloadOriginalActionButton(
                $templates['download_action'],
                translate('order_documents_dashboard_button_download_original_text', null, true),
                translate('order_documents_dashboard_button_download_original_title', null, true),
                null,
                'ep-icon ep-icon_download',
                [
                    'action'   => 'documents:envelope-grid:download-document',
                    'callback' => 'downloadEnvelopeDocument',
                    'envelope' => $envelopeId,
                    'document' => $envelope['documents']['original']['id'] ?? null,
                ],
                true
            ),
            new DatatableButtons\DownloadLatestActionButton(
                $templates['download_action'],
                translate('order_documents_dashboard_button_download_latest_text', null, true),
                translate('order_documents_dashboard_button_download_latest_title', null, true),
                null,
                'ep-icon ep-icon_download',
                [
                    'action'   => 'documents:envelope-grid:download-document',
                    'callback' => 'downloadEnvelopeDocument',
                    'envelope' => $envelopeId,
                    'document' => $envelope['documents']['latest']['id'] ?? null,
                ],
                true
            ),
        ]);

        $actionsPreview = new DatatablElements\Element($templates['actions']->withAttributes([
            'buttons' => $envelopeButtons->acceptsRow($envelope),
        ]));
        //endregion Actions

        return [
            'order'       => $orderPreview,
            'status'      => $statusPreview,
            'type'        => $typePreview,
            'sender'      => $senderPreview,
            'envelope'    => $envelopePreview,
            'showDetails' => $envelopeDetails,
            'description' => $descriptionPreview,
            'createdAt'   => getDateFormatIfNotEmpty($envelope['created_at_date'] ?? null),
            'updatedAt'   => getDateFormatIfNotEmpty($envelope['updated_at_date'] ?? null),
            'actions'     => $actionsPreview,
            'details'     => [
                [
                    'column' => 'Recipients',
                    'value'  => new DatatablElements\Element(
                        $templates['recipient_table']->withAttributes([
                            'rows'  => new DatatablElements\ElementList($recipientsList),
                        ])
                    ),
                ],
                ['column' => 'Information updated at', 'value' => getDateFormatIfNotEmpty($envelope['dispaly_info_updated_at_date'] ?? null)],
                ['column' => 'Status changed at',      'value' => getDateFormatIfNotEmpty($envelope['status_changed_at_date'] ?? null)],
                ['column' => 'First sending at',       'value' => getDateFormatIfNotEmpty($envelope['sent_original_at_date'] ?? null)],
                ['column' => 'Last sending at',        'value' => getDateFormatIfNotEmpty($envelope['sent_at_date'] ?? null)],
                [
                    'column' => 'Is signable',
                    'value'  => new DatatablElements\Element(
                        $templates['label']->withAttributes([
                            'text'  => $envelope['signing_enabled'] ? 'Yes' : 'No',
                            'type'  => $envelope['signing_enabled'] ? 'success' : 'default',
                        ])
                    ),
                ],
                ['column' => 'Completed at',           'value' => getDateFormatIfNotEmpty($envelope['completed_at_date'] ?? null)],
                ['column' => 'Declined at',            'value' => getDateFormatIfNotEmpty($envelope['declined_at_date'] ?? null)],
                ['column' => 'Deleted at',             'value' => getDateFormatIfNotEmpty($envelope['voided_at_date'] ?? null)],
                ['column' => 'Deleted reason',         'value' => cleanOutput($envelope['void_reason'] ?? null) ?: '&mdash;'],
            ],
        ];
    }

    /**
     * Gets the set of templates for envelope list entries.
     */
    private function getEnvelopeAdminListTemplates(): array
    {
        return [
            'label'               => new TextTemplate('<span class="label label-{{type}} fs-10">{{text}}</span>'),
            'badge'               => new TextTemplate('<span class="label label-{{color}} fs-10">{{text}}</span>'),
            'link_with_icon'      => new TextTemplate('<a class="{{class}}" href="{{url}}" title="{{title}}" target="_blank"></a>'),
            'recipient_table'     => new TextTemplate(
                <<<'TEMPLATE'
                <table class="table-bordered table-striped w-100pr no-footer">
                    <thead>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Sent at</th>
                        <th>Delivered at</th>
                        <th>Due date</th>
                        <th>Signed at</th>
                        <th>Declined at</th>
                        <th>Completed at</th>
                    </thead>
                    <tbody>
                        {{rows}}
                    </tbody>
                </table>
                TEMPLATE
            ),
            'recipient_entry'     => new TextTemplate(
                <<<'TEMPLATE'
                <tr>
                    <td class="w-30 tac vat">{{index}}</td>
                    <td class="w-200 vat">{{filter}}{{name}}</td>
                    <td class="w-200 tac vat">{{type}}</td>
                    <td class="w-200 tac vat">{{status}}{{appendix}}</td>
                    <td class="w-200 tac vat">{{action}}</td>
                    <td class="w-150 tac vat">{{sentAt}}</td>
                    <td class="w-150 tac vat">{{deliveredAt}}</td>
                    <td class="w-150 tac vat">{{dueDate}}</td>
                    <td class="w-150 tac vat">{{signedAt}}</td>
                    <td class="w-150 tac vat">{{declinedAt}}</td>
                    <td class="w-150 tac vat">{{completedAt}}</td>
                </tr>
                TEMPLATE
            ),
            'recipient_declining' => new TextTemplate(
                <<<'TEMPLATE'
                <div class="tal">
                    <strong>Declined by sender:</strong> {{type}}
                    <br>
                    <strong>Reason:</strong>
                    <br>
                    {{reason}}
                </div>
                TEMPLATE
            ),
            'text_with_filter'    => new TextTemplate(
                <<<'TEMPLATE'
                <div class="pull-left">{{filter}}</div>
                <div class="clearfix"></div>
                <span>{{text}}</span>
                TEMPLATE
            ),
            'filter'              => new TextTemplate(
                <<<'TEMPLATE'
                <a class="ep-icon ep-icon_filter txt-green dt-filter"
                    data-value-text="{{value.display}}"
                    data-value="{{value.real}}"
                    data-title="{{title}}"
                    data-name="{{name}}"
                    title="{{button.title}}">
                </a>
                TEMPLATE
            ),
            'details'             => new TextTemplate(
                <<<'TEMPLATE'
                <div class="pull-left">
                    <a title="View details" class="ep-icon ep-icon_plus fs-16 js-open-row-details"></a>
                </div>
                <div class="clearfix"></div>
                {{envelope}}
                TEMPLATE
            ),
            'description'         => new TextTemplate(
                <<<'TEMPLATE'
                <div class="grid-text">
                    <div class="grid-text__item">
                        {{text}}
                    </div>
                </div>
                TEMPLATE
            ),
            'envelope'            => new TextTemplate(
                <<<'TEMPLATE'
                <div>
                    <strong class="pull-left lh-16 pr-5">Title: </strong>
                    {{title}}
                </div>
                <div class="clearfix"></div>
                <div>
                    <strong class="pull-left lh-16 pr-5">Display type: </strong>
                    {{type}}
                </div>
                {{badges}}
                TEMPLATE
            ),
            'sender'              => new TextTemplate(
                <<<'TEMPLATE'
                <div class="pull-left">{{filter}}{{page}}</div>
                <div class="clearfix"></div>
                <span>{{name.current}}</span>
                <br>
                {{name.legal}}
                TEMPLATE
            ),
            'order'               => new TextTemplate(
                <<<'TEMPLATE'
                <div class="pull-left">
                    {{filter}}
                    <a class="ep-icon ep-icon_file-view fancybox.ajax fancyboxValidateModal"
                        data-title="Order Details"
                        title="View Order Details"
                        href="{{url}}">
                    </a>
                </div>
                <div class="clearfix"></div>
                <span>{{number}}</span>
                TEMPLATE
            ),
            'actions'             => new TextTemplate(
                <<<'TEMPLATE'
                <div class="dropdown">
                    <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></a>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                        {{buttons}}
                    </ul>
                </div>
                TEMPLATE,
            ),
            'popup_button'        => new TextTemplate(
                <<<'TEMPLATE'
                <li>
                    <a class="fancybox.ajax fancyboxValidateModal {{class}}"
                    data-fancybox-href="{{url}}"
                    data-title="{{popupTitle}}"
                    title="{{title}}">
                    <i class="{{icon}}"></i>
                    <span>{{text}}</span>
                    </a>
                </li>
                TEMPLATE
            ),
            'download_action'     => new TextTemplate(
                <<<'TEMPLATE'
                <li>
                    <a class="call-function {{class}}"
                        data-callback="{{data.callback}}"
                        data-js-action="{{data.action}}"
                        data-envelope="{{data.envelope}}"
                        data-document="{{data.document}}"
                        title="{{title}}">
                        <span class="{{icon}}"></span>
                        {{text}}
                    </a>
                </li>
                TEMPLATE
            ),
            'action'              => new TextTemplate(
                <<<'TEMPLATE'
                <li>
                    <a class="call-function {{class}}"
                        data-callback="{{data.callback}}"
                        data-js-action="{{data.action}}"
                        data-envelope="{{data.envelope}}"
                        title="{{title}}">
                        <span class="{{icon}}"></span>
                        {{text}}
                    </a>
                </li>
                TEMPLATE
            ),
        ];
    }
}

// End of file documents.php
// Location: /tinymvc/myapp/controllers/documents.php
