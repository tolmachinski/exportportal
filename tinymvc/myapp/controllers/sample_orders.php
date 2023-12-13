<?php

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Contracts\Group\GroupType;
use App\Common\Database\Exceptions\QueryException;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\DependencyException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\SampleOrders\DeliveryException;
use App\Common\Exceptions\SampleOrders\InvalidStatusException;
use App\Common\Exceptions\SampleOrders\PurchaseOrderConfirmationException;
use App\Common\Http\Request;
use App\Common\Traits\DocumentsApiAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ValidationException;
use App\Services\OderBillingService;
use App\Services\SampleOrdersAdminPageService;
use App\Services\SampleOrdersPageService;
use App\Services\SampleOrdersPopupService;
use App\Services\SampleOrdersService;
use App\Services\SampleServiceInterface;
use App\Services\SearchProductsFastService;
use App\Users\Person;
use App\Users\PersonalName;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\String\UnicodeString;

/**
 * Controller for sample orders.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Sample_Orders_Controller extends TinyMVC_Controller
{
    use DocumentsApiAwareTrait;

    private const SURVEY_POPUP_SAMPLE_ORDER = 10;
    private const SAMPLES_PER_PAGE = 5;
    private const PRODUCTS_PER_SEARCH = 10;
    private const SAMPLE_ORDER_MODULE = 35;
    private const ORDER_BILL_TYPE = 7;

    private const STATUS_ALIAS_NEW = 'new-order';
    private const STATUS_ALIAS_PAYMENT = 'payment-processing';
    private const STATUS_ALIAS_SHIPPING = 'shipping-in-progress';
    private const STATUS_ALIAS_COMPLETED = 'order-completed';
    private const STATUS_ALIAS_CANCELED = 'canceled';

    /**
     * Shows index page.
     */
    public function index(): void
    {
        headerRedirect('/sample_orders/my');
    }

    /**
     * Shows page with user order samples.
     */
    public function my(): void
    {
        checkIsLogged();
        checkDomainForGroup();
        checkGroupExpire();
        checkPermision('view_sample_orders', '/403');
        checkPermisionAndRedirect('monitor_sample_orders', '/sample_orders/administration');

        try {
            views(
                ['new/header_view', 'new/sample_orders/index_view', 'new/footer_view'],
                (new SampleOrdersPageService(
                    __SITE_LANG,
                    '/' . implode('/', array_slice(uri()->segments(), 0, 2)),
                    model(Sample_Orders_Model::class),
                    model(Sample_Orders_Statuses_Model::class),
                    self::SAMPLES_PER_PAGE
                ))->getPageInformation(request(), (int) privileged_user_id(), i_have_company())
            );
        } catch (NotFoundException | OutOfBoundsException $exception) {
            redirectWithMessage('/404', throwableToMessage($exception, 'The page is not found.'), 'errors');
        } catch (OwnershipException $exception) {
            redirectWithMessage('/403', throwableToMessage($exception, 'The page cannot be accessed.'), 'errors');
        }
    }

    /**
     * Shows the page for sample orders administrations.
     */
    public function administration(): void
    {
        checkIsLogged();
        checkPermision('monitor_sample_orders');
        checkPermision('view_sample_orders');

        /** @var Sample_Orders_Statuses_Model $sampleOrdersRepository */
        $sampleOrdersRepository = model(Sample_Orders_Statuses_Model::class);
        /** @var Ishippers_Model $shippersRepository */
        $shippersRepository = model(Ishippers_Model::class);
        $viewVariables = [
            'title'                  => 'Sample orders',
            'sample_order_statuses'  => $sampleOrdersRepository->findAll(),
            'international_shippers' => $shippersRepository->get_shippers(),
        ];

        views()->assign($viewVariables);
        views()->display('admin/header_view');
        views()->display('admin/sample_orders/index_view');
        views()->display('admin/footer_view');
    }

    /**
     * Regenerates the sample orders statuses texts.
     */
    public function generate_status_texts(): void
    {
        checkIsLogged();
        checkPermision('monitor_sample_orders');
        checkPermision('view_sample_orders');

        $statuses = [
            'new-order'            => [
                'buyer'  => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => <<<'MANDATORY'
                            <p>
                                You must wait for the seller to create a <strong>Purchase Order</strong>
                            </p>
                            <p>
                                You must update <strong>delivery address</strong> if sample order was not requested or created but assigned by the seller
                            </p>
                            <p>
                                You have to confirm the <strong>Purchase Order</strong> and the order’s status will be changed to <strong>Payment Processing</strong>.
                            </p>
                        MANDATORY,
                        'optional' 	=> <<<'OPTIONAL'
                            <p>
                                You can update <strong>delivery address</strong> after <strong>Purchase Order</strong> was created and/or updated, but this will require another <strong>Purchase Order</strong> update
                            </p>
                            <p>
                                You can negotiate with the seller about the <strong>Purchase Order</strong> using the <strong>Write to</strong> button and open conversation with the seller.
                            </p>
                            OPTIONAL,
                    ],
                ],
                'seller' => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => <<<'MANDATORY'
                            <p>You must create Purchase Order and add information:
                                <br>- due date
                                <br>- PO number
                                <br>- shipping method
                                <br>- order notes
                            </p>
                            MANDATORY,
                        'optional' 	=> <<<'OPTIONAL'
                            <p>
                                You can edit and the <strong>Purchase Order</strong> up until the buyer confirms it
                            </p>
                            <p>
                                You can negotiate with the buyer about the <strong>Purchase Order</strong> using the <strong>Write to</strong> button and open conversation with the buyer.
                            </p>
                            OPTIONAL,
                    ],
                ],
            ],
            'payment-processing'   => [
                'buyer'  => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => <<<'MANDATORY'
                            <p>
                                You should <strong>pay all the bills</strong> and wait until the Order Manager confirms all the payments.
                            </p>
                            MANDATORY,
                        'optional' 	=> null,
                    ],
                ],
                'seller' => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => <<<'MANDATORY'
                            <p>
                                You are waiting for the buyer to pay the order and receive a confirmation from the Order Manager.
                            </p>
                            MANDATORY,
                        'optional' 	=> null,
                    ],
                ],
            ],
            'shipping-in-progress' => [
                'buyer'  => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => '<p>You must <strong>confirm delivery</strong> upon order delivery.</p>',
                        'optional'  => null,
                    ],
                ],
                'seller' => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => <<<'MANDATORY'
                            <p>You must set <strong>delivery date</strong></p>
                            <p>You must update <strong>tracking information</strong>.</p>
                            MANDATORY,
                        'optional' 	=> null,
                    ],
                ],
            ],
            'order-completed'      => [
                'buyer'  => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => '<p>The order is completed.</p>',
                        'optional' 	=> null,
                    ],
                ],
                'seller' => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => '<p>The order is completed.</p>',
                        'optional' 	=> null,
                    ],
                ],
            ],
            'canceled'             => [
                'buyer'  => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => '<p>The order has been canceled.</p>',
                        'optional' 	=> null,
                    ],
                ],
                'seller' => [
                    'video' => null,
                    'text'  => [
                        'mandatory' => '<p>The order has been canceled.</p>',
                        'optional' 	=> null,
                    ],
                ],
            ],
        ];

        /** @var Sample_Orders_Statuses_Model $statusesRepository */
        $statusesRepository = model(Sample_Orders_Statuses_Model::class);
        foreach ($statuses as $key => $description) {
            $statusesRepository->update_one_by_alias($key, ['description' => $description]);
        }
    }

    /**
     * Returns data for administration datagrid.
     */
    public function ajax_dt_administration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('monitor_sample_orders');
        checkPermisionAjaxDT('view_sample_orders');

        try {
            $paginator = (new SampleOrdersAdminPageService())->getTableContent();

            jsonResponse('', 'success', [
                'sEcho'                => request()->request->getInt('sEcho', 0),
                'iTotalRecords'        => $paginator['total'] ?? 0,
                'iTotalDisplayRecords' => $paginator['total'] ?? 0,
                'aaData'               => $paginator['data'] ?? [],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        switch (uri()->segment(3)) {
            case 'request_order':
                checkPermisionAjaxModal('request_sample_order');

                $this->showRequestSampleOrderPopup(request(), (int) uri()->segment(4) ?: null, (int) privileged_user_id());

                break;
            case 'create_order':
                checkPermisionAjaxModal('create_sample_order');

                $this->showCreateSampleOrderPopup(request(), (int) uri()->segment(4) ?: null, (int) privileged_user_id());

                break;
            case 'assign_order':
                checkPermisionAjaxModal('assign_sample_order');

                $this->showAssignSampleOrderPopup(request());

                break;
            case 'order_details':
                checkPermisionAjaxModal('monitor_sample_orders');

                $this->showSampleOrderDetailsPopup((int) uri()->segment(4));

                break;
            case 'edit_purchase_order':
                checkPermisionAjaxModal('create_sample_order');
                checkPermisionAjaxModal('edit_sample_order');

                $this->showEditPurchaseOrderPopup(request(), (int) uri()->segment(4) ?: null, (int) privileged_user_id());

                break;
            case 'view_purchase_order':
                checkPermisionAjaxModal('view_sample_orders');

                $this->showViewPurchaseOrderPopup(request(), (int) uri()->segment(4) ?: null, (int) privileged_user_id());

                break;
            case 'delivery_address':
                checkPermisionAjaxModal('edit_sample_order');
                checkPermisionAjaxModal('set_delivery_address_to_sample_order');

                $this->showDeliveryAddressPopup(request(), (int) uri()->segment(4), privileged_user_id());

                break;
            case 'timeline':
                checkPermisionAjaxModal('view_sample_order_timeline');

                $this->showOrderTimelinePopup((int) uri()->segment(4), privileged_user_id());

                break;
            case 'tracking_info':
                checkPermisionAjaxModal('modify_sample_order_tracking_info');

                $this->showTrackingInfoPopup((int) uri()->segment(4), privileged_user_id());

                break;
            case 'bill_list':
                checkPermisionAjaxModal('request_sample_order');
                checkPermisionAjaxModal('view_sample_orders');

                $this->showBillListPopup((int) uri()->segment(4) ?: null, (int) privileged_user_id());

                break;

            default:
                messageInModal('The provided path is not found on the server');

                break;
        }
    }

    /**
     * Handles ajax operations.
     */
    public function ajax_operations(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $request = request();
        $userId = (int) privileged_user_id();

        switch (uri()->segment(3)) {
            case 'sample':
                checkPermisionAjax('read_sample_order');

                $this->showSampleOrder($request, (int) $request->request->getInt('order', 0) ?: null, $userId, i_have_company());

                break;
            case 'counters':
                checkPermisionAjax('read_sample_order');

                $this->countStatuses($request, $userId, i_have_company());

                break;
            case 'find_orders':
                checkPermisionAjax('read_sample_order');

                $this->findSampleOrders($request, $userId, i_have_company());

                break;
            case 'create_order':
                checkPermisionAjax('create_sample_order');

                $this->createSampleOrder(
                    $request,
                    $this->get(SampleOrdersService::class),
                    $userId,
                    $request->request->get('room') ?: null,
                    $request->request->getInt('recipient') ?: null
                );

                break;
            case 'assign_order':
                checkPermisionAjax('assign_sample_order');

                $this->assignSampleOrder(
                    $this->get(SampleOrdersService::class),
                    $userId,
                    $request->request->get('order') ?: null,
                    $request->request->get('room') ?: null,
                    $request->request->getInt('recipient') ?: null,
                );

                break;
            case 'send_request':
                checkPermisionAjax('request_sample_order');

                $this->requestSampleOrder($request, $this->get(SampleOrdersService::class), $userId);

                break;
            case 'find_products':
                checkPermisionAjax('view_sample_orders');

                $this->findProducts($userId, $request->request->get('search'));

                break;
            case 'confirm_payment':
                checkPermisionAjax('administrate_orders');

                $this->confirmOrderPayment($this->get(SampleOrdersService::class), $request->request->getInt('sample_order_id'));

                break;
            case 'set_delivery_address':
                checkPermisionAjax('edit_sample_order');
                checkPermisionAjax('set_delivery_address_to_sample_order');

                $this->changeDeliveryAddress($request, $this->get(SampleOrdersService::class), $request->request->getInt('id_order'), $userId);

                break;
            case 'edit_tracking_info':
                checkPermisionAjax('modify_sample_order_tracking_info');

                $this->editTrackingInfo($request, $this->get(SampleOrdersService::class), $request->request->getInt('id_order'), $userId);

                break;
            case 'edit_purchase_order':
                checkPermisionAjax('create_sample_order');
                checkPermisionAjax('edit_sample_order');

                $this->editPurchaseOrder($request, $this->get(SampleOrdersService::class), $request->request->getInt('order') ?: null, $userId);

                break;
            case 'confirm_purchase_order':
                checkPermisionAjax('request_sample_order');
                checkPermisionAjax('edit_sample_order');

                $this->confirmPurchaseOrder($request, $this->get(SampleOrdersService::class), $request->request->getInt('order') ?: null, $userId);

                break;
            case 'confirm_delivery':
                checkPermisionAjax('request_sample_order');
                checkPermisionAjax('edit_sample_order');

                $this->confirmOrderDelivery($this->get(SampleOrdersService::class), $request->request->getInt('order') ?: null, $userId);

                break;

            default:
                json(['message' => 'The provided path is not found on the server', 'mess_type' => 'error'], 404);

                break;
        }
    }

    /**
     * Shows the popup modal where buyer can make sample request.
     */
    private function showRequestSampleOrderPopup(Request $request, ?int $itemId, int $userId): void
    {
        try {
            views()->display(
                'new/sample_orders/popups/request_sample_order_view',
                (new SampleOrdersPopupService())->getRequestPopupInformation($request, $userId, $itemId)
            );
        } catch (NotFoundException $exception) {
            messageInModal(
                throwableToMessage(
                    $exception,
                    SampleOrdersPopupService::USER_NOT_FOUND_ERROR === $exception->getCode()
                        ? translate('systmess_error_user_does_not_exist')
                        : translate('systmess_error_item_does_not_exist')
                )
            );
        } catch (OwnershipException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }
    }

    /**
     * Sample order details for admin.
     */
    private function showSampleOrderDetailsPopup(int $orderId): void
    {
        try {
            views()->display(
                'admin/sample_orders/popup_details_view',
                (new SampleOrdersPageService(
                    __SITE_LANG,
                    '/' . implode('/', array_slice(uri()->segments(), 0, 2)),
                    model(Sample_Orders_Model::class),
                    model(Sample_Orders_Statuses_Model::class),
                    self::SAMPLES_PER_PAGE
                ))->getSampleInformationForAdmin($orderId)
            );
        } catch (NotFoundException | OutOfBoundsException $exception) {
            messageInModal(throwableToMessage($exception, 'The sample order is not found.'));
        } catch (OwnershipException $exception) {
            messageInModal(throwableToMessage($exception, 'This sample order cannot be accessed.'));
        }
    }

    /**
     * Shows the modal popup where user can create the sample order.
     */
    private function showCreateSampleOrderPopup(Request $request, ?int $itemId, int $userId): void
    {
        if (null !== $itemId) {
            try {
                $details = (new SampleOrdersPopupService())->getCreateOrderPopupInformation($request, $userId, $itemId);
                if ($itemId !== (int) $details['item']['id_seller'] ?? 0) {
                    throw new OwnershipException('Seller can create sample orders only for his own items.', SampleServiceInterface::ITEM_OWNERSHIP_ERROR);
                }

                views()->display('new/sample_orders/popups/create_item_sample_order_view', $details);
            } catch (NotFoundException $exception) {
                messageInModal(
                    throwableToMessage(
                        $exception,
                        SampleOrdersPopupService::USER_NOT_FOUND_ERROR === $exception->getCode()
                            ? translate('systmess_error_user_does_not_exist')
                            : translate('systmess_error_item_does_not_exist')
                    )
                );
            } catch (OwnershipException $exception) {
                messageInModal(throwableToMessage($exception, translate('sample_orders_create_access_denied_only_seller')));
            } catch (AccessDeniedException $exception) {
                messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
            }
        } else {
            /** @var MatrixConnector $matriConnector */
            $matriConnector = container()->get(MatrixConnector::class);
            $userId = $request->query->get('user') ?? null;

            views()->display('new/sample_orders/popups/create_sample_order_view', [
                'room'      => $request->query->get('room'),
                'isDialog'  => (bool) $request->query->get('dialog', 0),
                'recipient' => null === $userId ? null : $matriConnector
                    ->getUserReferenceProvider()
                    ->getReferenceByUserMxid($userId)['id_user'] ?? null,
            ]);
        }
    }

    /**
     * Shows the popup modal where seller can assign a sample order to message theme.
     */
    private function showAssignSampleOrderPopup(Request $request): void
    {
        /** @var MatrixConnector $matriConnector */
        $matriConnector = container()->get(MatrixConnector::class);

        views()->display(
            'new/sample_orders/popups/assign_sample_view',
            [
                'room'      => $request->query->get('room'),
                'recipient' => $matriConnector
                    ->getUserReferenceProvider()
                    ->getReferenceByUserMxid($request->query->get('user'))['id_user'] ?? null,
            ]
        );
    }

    /**
     * Shows the popup modal where seller can create PO (purchase order).
     */
    private function showEditPurchaseOrderPopup(Request $request, ?int $orderId, int $userId): void
    {
        try {
            // Fetch details for popup
            $details = (new SampleOrdersPopupService())->getEditPurchaseOrderInformation(
                $userId,
                $orderId,
                [static::STATUS_ALIAS_NEW]
            );
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (PurchaseOrderConfirmationException $exception) { // Stop if already confirmed
            messageInModal(throwableToMessage($exception, translate('sample_orders_edit_po_is_confirmed_message')), 'info');
        } catch (OwnershipException | AccessDeniedException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }
        /** @var Ishippers_Model $shippersRepository */
        $shippersRepository = model(Ishippers_Model::class);

        views()->display('new/sample_orders/popups/edit_purchase_order_view', array_merge(
            $details,
            [
                'shippers'  => $shippersRepository->get_shippers() ?? [],
                'is_dialog' => (bool) $request->query->getInt('dialog', 0),
            ]
        ));
    }

    /**
     * Shows the popup modal where user can pay bills.
     */
    private function showBillListPopup(?int $orderId, int $userId): void
    {
        try {
            $billingDetails = (new SampleOrdersPopupService())->getBillsPopupInfomration(
                $this->get(SampleOrdersService::class),
                model(User_Bills_Model::class),
                $userId,
                $orderId,
                static::STATUS_ALIAS_PAYMENT
            );
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (OwnershipException | AccessDeniedException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        list('bills' => $bills) = $billingDetails;
        if (empty($bills)) {
            messageInModal(translate('sample_orders_errors_bills_list_empty'), 'info');
        }

        views()->display('new/sample_orders/popups/preview_order_bills_view', $billingDetails);
    }

    /**
     * Shows the popup modal where user can see PO (purchase order) details and confirm it if can.
     */
    private function showViewPurchaseOrderPopup(Request $request, ?int $orderId, int $userId): void
    {
        try {
            // Fetch PO details.
            $details = (new SampleOrdersPopupService())->getPurchaseOrderInformation($userId, $orderId);
            $isNewOrder = static::STATUS_ALIAS_NEW === $details['status']['alias'];
            $allowActions = $isNewOrder && !$details['is_confirmed'];
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (OwnershipException | AccessDeniedException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        views()->display('new/sample_orders/popups/preview_purchase_order_view', array_merge(
            $details,
            [
                'can_edit'    => $allowActions && $details['is_edited'] && have_right('create_sample_order'),
                'is_dialog'   => (bool) $request->query->getInt('dialog', 0),
                'can_confirm' => $allowActions && $details['is_confirmable'] && $details['is_deliverable'] && have_right('request_sample_order'),
            ]
        ));
    }

    /**
     * Shows the popup modal where buyer can write delivery address.
     */
    private function showDeliveryAddressPopup(Request $request, int $orderId, int $buyerId): void
    {
        try {
            $details = (new SampleOrdersPopupService())->getDeliveryAddressPopupInformation($orderId, $buyerId, [
                static::STATUS_ALIAS_NEW,
            ]);
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (PurchaseOrderConfirmationException $exception) { // Stop if already confirmed
            messageInModal(throwableToMessage($exception, translate('sample_orders_edit_delivey_address_is_confirmed_message')), 'info');
        } catch (OwnershipException | AccessDeniedException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        views()->display('new/sample_orders/popups/edit_delivery_address_view', array_merge(
            $details,
            [
                'is_dialog' => (bool) $request->query->getInt('dialog', 0),
            ]
        ));
    }

    /**
     * Show sample order timeline in popup.
     */
    private function showOrderTimelinePopup(int $orderId, int $userId): void
    {
        try {
            views()->display(
                'new/sample_orders/popups/timeline_view',
                (new SampleOrdersPopupService())->getTimelinePopupInformation($orderId, $userId)
            );
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (OwnershipException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }
    }

    /**
     * Show sample order tracking info in popup.
     */
    private function showTrackingInfoPopup(int $orderId, int $userId): void
    {
        try {
            views()->display(
                'new/sample_orders/popups/edit_tracking_info_view',
                (new SampleOrdersPopupService())->getTrackingInfoPopupInformation($orderId, $userId)
            );
        } catch (NotFoundException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (OwnershipException $exception) {
            messageInModal(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }
    }

    /**
     * Counts the sample statuses.
     */
    private function countStatuses(Request $request, int $userId, bool $isSeller): void
    {
        $statuses = (new SampleOrdersPageService(
            __SITE_LANG,
            $request->getPathInfo(),
            model(Sample_Orders_Model::class),
            model(Sample_Orders_Statuses_Model::class),
            self::SAMPLES_PER_PAGE
        ))->getSampleStatusesForUser($userId, $isSeller)['statuses'] ?? [];

        jsonResponse(null, 'success', [
            'counters' => array_merge($statuses, ['all' => array_sum($statuses)]),
        ]);
    }

    /**
     * Finds products by given search text.
     */
    private function findProducts(int $userId, ?string $searchText): void
    {
        try {
            $params = ['samples' => 1];

            if (
                false !== strpos($searchText, __SITE_URL . 'item/')
                && 0 < $itemId = id_from_link($searchText)
            ) {
                $params['list_item'] = [$itemId];
            } else {
                $params['keywords'] = $searchText;
            }

            $paginator = (new SearchProductsFastService(static::PRODUCTS_PER_SEARCH))->findElasticProducts($userId, $params);
            $delimiter = '<!-- delimiter -->';

            $products = array_filter(array_map(
                'trim',
                preg_split('/' . preg_quote($delimiter, '/') . '/', views()->fetch('new/sample_orders/products_list_view', [
                    'products'  => arrayPull($paginator, 'data', []),
                    'delimiter' => $delimiter,
                ]))
            ));
        } catch (NotFoundException | OutOfBoundsException | OwnershipException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(null, 'success', [
            'data'      => $products,
            'paginator' => arrayCamelizeAssocKeys($paginator),
        ]);
    }

    /**
     * Finds the samples.
     */
    private function findSampleOrders(Request $request, int $userId, bool $isSeller): void
    {
        try {
            $paginator = (new SampleOrdersPageService(
                __SITE_LANG,
                $request->getPathInfo(),
                model(Sample_Orders_Model::class),
                model(Sample_Orders_Statuses_Model::class),
                self::SAMPLES_PER_PAGE
            ))->findSamples($request, $userId, $isSeller);

            $delimiter = '<!-- delimiter -->';
            $samples = array_filter(array_map(
                'trim',
                preg_split('/' . preg_quote($delimiter, '/') . '/', views()->fetch('new/sample_orders/samples_list_view', [
                    'samples'   => arrayPull($paginator, 'data', []),
                    'is_seller' => $isSeller,
                    'delimiter' => $delimiter,
                ]))
            ));
        } catch (NotFoundException | OutOfBoundsException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_sample_page_not_found')));
        } catch (OwnershipException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(null, 'success', [
            'data'      => $samples,
            'paginator' => arrayCamelizeAssocKeys($paginator),
        ]);
    }

    /**
     * Shows information about sample.
     */
    private function showSampleOrder(Request $request, ?int $orderId, int $userId, bool $isSeller): void
    {
        try {
            $details = (new SampleOrdersPageService(
                __SITE_LANG,
                $request->getPathInfo(),
                model(Sample_Orders_Model::class),
                model(Sample_Orders_Statuses_Model::class),
                self::SAMPLES_PER_PAGE
            ))->getSampleInformation($request, $orderId, $userId, $isSeller);
        } catch (NotFoundException | OutOfBoundsException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (OwnershipException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(null, 'success', [
            'data'  => null === $details ? null : views()->fetch('new/sample_orders/sample_details_view', $details),
            'title' => translate('sample_orders_texts_order_details_title', [
                '{{ORDER}}' => orderNumber($orderId),
            ]),
        ]);
    }

    /**
     * Allows seller to create sample order.
     */
    private function createSampleOrder(Request $request, SampleOrdersService $sampleOrders, int $userId, ?string $roomId, ?int $buyerId): void
    {
        try {
            $orderId = null;
            $itemId = $items = $request->request->get('item') ?? null;
            $messages = new ParameterBag(['note' => translate('sample_orders_timeline_notes_created')]);
            $user = (new Person($userId))
                ->withName(new PersonalName(\user_name_session()))
                ->withGroupName(\group_name_session())
                ->withGroupType(GroupType::tryFrom(\user_group_type()))
            ;
            if (is_array($items)) {
                $sampleMetadata = $sampleOrders->createOrderForItems(
                    $request,
                    $user,
                    $items,
                    $buyerId,
                    $roomId,
                    $messages
                );
            } else {
                $sampleMetadata = $sampleOrders->createOrderFromItem(
                    $request,
                    $user,
                    (int) $itemId ?: null,
                    $buyerId,
                    $roomId,
                    $messages
                );
            }

            list('id' => $orderId) = $sampleMetadata;
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            $fallback_messages = [
                SampleServiceInterface::USER_NOT_FOUND_ERROR  => translate('systmess_error_user_does_not_exist'),
                SampleServiceInterface::ITEM_NOT_FOUND_ERROR  => translate('systmess_error_item_does_not_exist'),
            ];

            jsonResponse(throwableToMessage($exception, $fallback_messages[$exception->getCode()] ?? null));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (TypeError | RuntimeException | Exception $exception) {
            jsonResponse(
                throwableToMessage($exception, translate('sample_orders_create_unknown_error_public_text')),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }

        jsonResponse(translate('sample_orders_create_success_public_text'), 'success', [
            'title' => translate('sample_orders_create_success_public_title', ['{{ORDER}}' => orderNumber($orderId)]),
            'data'  => ['order' => is_array($itemId) ? null : $orderId],
            'urls'  => is_array($itemId) ? [] : [
                ['href' => getUrlForGroup("/sample_orders/my/order/{$orderId}"), 'type' => 'redirect'],
            ],
        ]);
    }

    /**
     * Set sample order delivery address.
     */
    private function requestSampleOrder(Request $request, SampleOrdersService $sampleOrders, int $userId): void
    {
        try {
            list('id' => $orderId) = $sampleOrders->requestOrderFromItem(
                $request,
                (new Person($userId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                $request->request->get('item') ?? null,
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_created')]),
                'custom' === $request->request->get('address_type')
            );
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            jsonResponse(
                throwableToMessage(
                    $exception,
                    SampleOrdersPopupService::USER_NOT_FOUND_ERROR === $exception->getCode()
                        ? translate('systmess_error_user_does_not_exist')
                        : translate('systmess_error_item_does_not_exist')
                )
            );
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (TypeError | RuntimeException | Exception $exception) {
            jsonResponse(
                throwableToMessage($exception, translate('sample_orders_request_unknown_error_public_text')),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }

        jsonResponse(translate('sample_orders_request_success_public_text'), 'success', [
            'title' => translate('sample_orders_request_success_public_title', ['{{ORDER}}' => orderNumber($orderId)]),
            'data'  => ['order' => $orderId],
            'urls'  => [
                ['href' => getUrlForGroup("/sample_orders/my/order/{$orderId}"), 'type' => 'redirect'],
            ],
        ]);
    }

    /**
     * Allows seller to assign sample order.
     */
    private function assignSampleOrder(SampleOrdersService $sampleOrders, int $userId, ?string $orderNumber, ?string $roomId, ?int $buyerId): void
    {
        try {
            $orderId = orderNumberToId($orderNumber);
            $order = $sampleOrders->getSampleOrderInformation($orderId, $userId);
            if (static::STATUS_ALIAS_NEW !== ($order['status']['alias'] ?? null)) {
                throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
            }

            if (!empty($order['id_buyer'])) {
                jsonResponse(translate('systmess_error_buyer_already_assigned_sample_order'));
            }

            $sampleOrders->assignOrder(
                $orderId,
                new ParameterBag(\arrayCamelizeAssocKeys($order)),
                (new Person($userId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                $buyerId,
                $roomId,
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_assigned')])
            );
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            $fallback_messages = [
                SampleServiceInterface::USER_NOT_FOUND_ERROR  => translate('systmess_error_user_does_not_exist'),
                SampleServiceInterface::ORDER_NOT_FOUND_ERROR => translate('systmess_error_sample_order_does_not_exist'),
            ];

            jsonResponse(throwableToMessage($exception, $fallback_messages[$exception->getCode()] ?? null));
        } catch (DependencyException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_already_assigned_sample_order')));
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (TypeError | RuntimeException | Exception $exception) {
            jsonResponse(
                throwableToMessage($exception, translate('sample_orders_assign_unknown_error_public_text')),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }

        jsonResponse(translate('sample_orders_assignment_success_text'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Set delivery address.
     */
    private function changeDeliveryAddress(Request $request, SampleOrdersService $sampleOrders, ?int $orderId, int $buyerId): void
    {
        try {
            $order = $sampleOrders->getSampleOrderInformation($orderId, $buyerId);
            $sampleOrders->setDeliveryAddress(
                $request,
                new ParameterBag(\arrayCamelizeAssocKeys($order)),
                (new Person($buyerId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_delivery_address_changed')]),
                [static::STATUS_ALIAS_NEW],
                'custom' === $request->request->get('address_type')
            );
        } catch (QueryException $exception) {
            jsonResponse(translate('systmess_internal_server_error'), 'warning');
        } catch (NotFoundException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (PurchaseOrderConfirmationException $exception) { // Stop if already confirmed
            messageInModal(throwableToMessage($exception, translate('sample_orders_edit_delivey_address_is_confirmed_message')), 'info');
        } catch (OwnershipException | AccessDeniedException $exception) { // Handles access exceptions
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(translate('sample_orders_delivery_address_change_success_text'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Edits the sample order purchase order (PO).
     */
    private function editPurchaseOrder(Request $request, SampleOrdersService $sampleOrders, ?int $orderId, int $userId): void
    {
        try {
            $order = $sampleOrders->getSampleOrderInformation($orderId, $userId);
            //Now we take parts of the order
            $purchaseOrder = $order['purchase_order'] ?? [];
            $purchasedProducts = \arrayByKey($order['purchased_products'] ?? [], 'item_id');
            $purchaseOrderTimeline = new ArrayCollection($order['purchase_order_timeline'] ?? []);
            $isFirstEdit = false === $purchaseOrder['is_edited'] ?? false;

            // And update PO
            $sampleOrders->editPurchaseOrder(
                $orderId,
                \arrayCamelizeAssocKeys($order),
                $purchaseOrder,
                $purchasedProducts,
                $purchaseOrderTimeline,
                $request,
                (new Person($userId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                $request->request->get('products') ?? [],
                [static::STATUS_ALIAS_NEW],
                new ParameterBag(
                    [
                        'note' => translate($isFirstEdit ? 'sample_orders_timeline_notes_po_created' : 'sample_orders_timeline_notes_po_edited'),
                    ]
                )
            );
        } catch (QueryException $exception) {
            jsonResponse(translate('systmess_internal_server_error'), 'warning');
        } catch (NotFoundException $exception) {
            $fallbackMessages = [
                SampleServiceInterface::USER_NOT_FOUND_ERROR  => translate('systmess_error_user_does_not_exist'),
                SampleServiceInterface::ITEM_NOT_FOUND_ERROR  => translate('systmess_error_item_does_not_exist'),
                SampleServiceInterface::ORDER_NOT_FOUND_ERROR => translate('systmess_error_sample_order_does_not_exist'),
            ];

            jsonResponse(throwableToMessage($exception, $fallbackMessages[$exception->getCode()] ?? null));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (PurchaseOrderConfirmationException $exception) { // Stop if already confirmed
            messageInModal(throwableToMessage($exception, translate('sample_orders_edit_po_is_confirmed_message')), 'info');
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (RuntimeException | TypeError | Exception $exception) {
            jsonResponse(
                throwableToMessage(
                    $exception,
                    translate($isFirstEdit ? 'sample_orders_po_create_unknown_error_public_text' : 'sample_orders_po_edit_unknown_error_public_text')
                ),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }

        jsonResponse(translate($isFirstEdit ? 'sample_orders_po_create_success_text' : 'sample_orders_po_edit_success_text'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Confirms the sample order purchase order (PO).
     */
    private function confirmPurchaseOrder(Request $request, SampleOrdersService $sampleOrders, ?int $orderId, int $userId): void
    {
        //region Services
        // Create billing service
        $billing = new OderBillingService(
            model(User_Bills_Model::class),
            new DateInterval(sprintf('P%sD', config('default_bill_period', 7))),
            new DecimalMoneyFormatter(new ISOCurrencies()),
            new UnicodeString(translate('sample_orders_bill_description', ['{{ORDER}}' => orderNumber($orderId)])),
            static::ORDER_BILL_TYPE
        );
        /** @var Sample_Orders_Statuses_Model $statusesRepository */
        $statusesRepository = model(Sample_Orders_Statuses_Model::class);
        //endregion Services

        //region Confirm PO
        try {
            // Get sample order
            $order = $sampleOrders->getSampleOrderInformation($orderId, $userId);
            // Take the parts of the order that will be used later
            $purchase_order = $order['purchase_order'] ?? [];
            $purchase_order_timeline = new ArrayCollection($order['purchase_order_timeline'] ?? []);
            // Make persons instances (better for incapsulation thatn the bare int and string values)
            $buyer = (new Person($userId))
                ->withName(new PersonalName(\user_name_session()))
                ->withGroupName(\group_name_session())
                ->withGroupType(GroupType::tryFrom(\user_group_type()))
            ;

            $sampleOrders->confirmPurchaseOrder(
                $orderId,
                \arrayCamelizeAssocKeys($order),
                $purchase_order,
                $purchase_order_timeline,
                $buyer,
                (int) $statusesRepository->find_by_alias(static::STATUS_ALIAS_PAYMENT)['id'],
                [static::STATUS_ALIAS_NEW],
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_confirmed_po')])
            );

            // Create order bill for user to pay
            $billing->createOneBill($buyer, $orderId, null, $order['price'], $order['final_price']);
        } catch (NotFoundException $exception) { // Handles not found exceptions
            $fallback_messages = [
                SampleServiceInterface::ORDER_NOT_FOUND_ERROR => translate('systmess_error_sample_order_does_not_exist'),
            ];

            jsonResponse(throwableToMessage($exception, $fallback_messages[$exception->getCode()] ?? null));
        } catch (PurchaseOrderConfirmationException $exception) { // Stop if if cannot be confirmed right now
            jsonResponse(throwableToMessage($exception, translate('sample_orders_confirm_po_not_confirmable_message')), 'warning');
        } catch (OwnershipException | AccessDeniedException $exception) { // Handles access exceptions
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (TypeError | QueryException | RuntimeException $exception) { //  Handles other runtime exceptions
            jsonResponse(
                throwableToMessage($exception, translate('sample_orders_confirm_po_unknown_error_public_text')),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }
        //endregion Confirm PO

        jsonResponse(translate('sample_orders_po_confirmation_success_text'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Confirm order payment.
     */
    private function confirmOrderPayment(SampleOrdersService $sampleOrders, int $orderId): void
    {
        /** @var Sample_Orders_Statuses_Model $statusesRepository */
        $statusesRepository = model(Sample_Orders_Statuses_Model::class);

        try {
            $order = $sampleOrders->getSampleOrderInformation($orderId, null, false, false, false, true);
            if (static::STATUS_ALIAS_PAYMENT !== ($order['status']['alias'] ?? null)) {
                throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
            }

            $sampleOrders->confirmOrderPayment(
                $orderId,
                new ParameterBag(\arrayCamelizeAssocKeys($order)),
                (int) $statusesRepository->find_by_alias(static::STATUS_ALIAS_SHIPPING)['id'],
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_payments_confirmed')])
            );
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            jsonResponse(
                throwableToMessage(
                    $exception,
                    SampleOrdersService::ORDER_NOT_FOUND_ERROR === $exception->getCode()
                        ? translate('systmess_error_sample_order_does_not_exist')
                        : translate('systmess_error_sample_order_bills_not_exist')
                )
            );
        } catch (DependencyException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_confirm_sample_order_payment_not_confirmed_bill')), 'warning');
        } catch (OwnershipException | AccessDeniedException $exception) { // Handles access exceptions
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(translate('sample_orders_payments_confirmation_success'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Edit tracking info.
     */
    private function editTrackingInfo(Request $request, SampleOrdersService $sampleOrders, int $orderId, int $userId): void
    {
        try {
            $order = $sampleOrders->getSampleOrderInformation($orderId, null, false, false, false, true);
            if (static::STATUS_ALIAS_SHIPPING !== ($order['status']['alias'] ?? null)) {
                throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
            }

            $sampleOrders->editTrackingInfo(
                $request,
                $orderId,
                new ParameterBag(\arrayCamelizeAssocKeys($order)),
                (new Person($userId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                new ParameterBag(['note' => translate('sample_orders_timeline_notes_tracking_info_changed')])
            );
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }

        jsonResponse(translate('sample_orders_tracking_info_update_success'), 'success', [
            'data' => [
                'order' => $orderId,
            ],
        ]);
    }

    /**
     * Confirms delivery of the sample order.
     */
    private function confirmOrderDelivery(SampleOrdersService $sampleOrders, ?int $orderId, int $userId): void
    {
        // Create sample roders service
        /** @var Sample_Orders_Statuses_Model $statusesRepository */
        $statusesRepository = model(Sample_Orders_Statuses_Model::class);

        try {
            // Get order and check for valid status
            $order = $sampleOrders->getSampleOrderInformation($orderId, $userId);
            if (static::STATUS_ALIAS_SHIPPING !== ($order['status']['alias'] ?? null)) {
                throw new InvalidStatusException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
            }

            // And confirm order delivery
            $sampleOrders->confirmOrderDelivery(
                $orderId,
                new ParameterBag(\arrayCamelizeAssocKeys($order)),
                (new Person($userId))
                    ->withName(new PersonalName(\user_name_session()))
                    ->withGroupName(\group_name_session())
                    ->withGroupType(GroupType::tryFrom(\user_group_type())),
                new ParameterBag(['note' => translate('sample_orders_delivery_confirmed')]),
                (int) $statusesRepository->find_by_alias(static::STATUS_ALIAS_COMPLETED)['id']
            );
        } catch (QueryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_internal_server_error')));
        } catch (NotFoundException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_sample_order_does_not_exist')));
        } catch (DeliveryException $exception) {
            jsonResponse(throwableToMessage($exception, translate('sample_orders_confirm_delivery_date_required')));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (OwnershipException | AccessDeniedException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        } catch (RuntimeException | TypeError $exception) {
            jsonResponse(
                throwableToMessage($exception, translate('sample_orders_delivery_confirmation_unknown_error_public_text')),
                !DEBUG_MODE ? 'warning' : 'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($exception)]]
            );
        }

        //region Survey popup
        $openSurveyPopup = false;
        if (!admin_logged_as_user()) {
            /** @var User_Popups_Model $popupSurveys */
            $popupSurveys = model(User_Popups_Model::class);
            $checkRecordingExist = $popupSurveys->findOneBy([
                'columns'    => 'id, is_viewed',
                'conditions' => [
                    'filter_by' => [
                        'id_user'   => session()->id,
                        'id_popup'  => static::SURVEY_POPUP_SAMPLE_ORDER,
                    ],
                ],
            ]);
            $openSurveyPopup = true;

            if (isset($checkRecordingExist)) {
                if (0 === $checkRecordingExist['is_viewed']) {
                    // viewed order_sample_survey
                    $popupSurveys->updateOne($checkRecordingExist['id'], [
                        'is_viewed' => 1,
                        'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                    ]);
                    widgetPopupsSystemRemoveOneItem("order_sample_survey");
                } else {
                    $openSurveyPopup = false;
                }
            } else {
                // insert order_sample_survey
                $popupSurveys->insertOne([
                    'id_user'   => session()->id,
                    'id_popup'  => static::SURVEY_POPUP_SAMPLE_ORDER,
                    'is_viewed' => 1,
                    'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                ]);
                widgetPopupsSystemRemoveOneItem("order_sample_survey");
            }
        }
        //endregion Survey popup

        jsonResponse(translate('sample_orders_delivery_confirmation_success'), 'success', [
            'data' => [
                'order'           => $orderId,
                'openSurveyPopup' => $openSurveyPopup,
            ],
        ]);
    }
}

// End of file sample_orders.php
// Location: /tinymvc/myapp/controllers/sample_orders.php
