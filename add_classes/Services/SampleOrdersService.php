<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Contracts\Group\GroupType;
use App\Common\Database\Exceptions\QueryException;
use App\Common\Database\Relations\RelationInterface;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\DependencyException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\QueryException as LegacyQueryException;
use App\Common\Exceptions\SampleOrders\DeliveryException;
use App\Common\Exceptions\SampleOrders\InvalidStatusException;
use App\Common\Exceptions\SampleOrders\PurchaseOrderConfirmationException;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\Standalone\SequenceValidator;
use App\Common\Validation\ValidationException;
use App\Common\Validation\ValidatorInterface;
use App\Messenger\Message\Command\AddSampleOrderDocument;
use App\Messenger\Message\Command\CreateDirectMatrixChatRoom;
use App\Users\Contracts\PersonInterface;
use App\Validators\AddressValidator;
use App\Validators\RequestSampleOrderValidator;
use App\Validators\SampleOrderDeliveryDateValidator;
use App\Validators\SampleOrderItemValidator;
use App\Validators\SampleOrderTrackingInfoValidator;
use App\Validators\SampleOrderValidator;
use App\Validators\SimplifiedPurchaseOrderValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ExportPortal\Bridge\Matrix\Notifier\Notification\MatrixNotification;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Notifier\NotifierInterface;

use const App\Common\GENERIC_UUID_NAMESPACE;

final class SampleOrdersService implements SampleServiceInterface
{
    private const MESSAGES_SAMPLE_ORDER_CREATE_TEXT = 'sample_orders_messages_created_by_seller';
    private const MESSAGES_SAMPLE_ORDER_CREATE_TITLE = 'sample_orders_messages_created_by_seller_title';
    private const MESSAGES_SAMPLE_ORDER_ASSIGN_TEXT = 'sample_orders_messages_assigned_to_buyer';
    private const MESSAGES_SAMPLE_ORDER_ASSIGN_TITLE = 'sample_orders_messages_assigned_to_buyer_title';
    private const MESSAGES_SAMPLE_ORDER_REQUEST_TEXT = 'sample_orders_messages_created_by_buyer';
    private const MESSAGES_SAMPLE_ORDER_REQUEST_TITLE = 'sample_orders_messages_created_by_buyer_title';
    private const MESSAGES_SAMPLE_ORDER_PO_EDIT_TEXT = 'sample_orders_messages_po_edited';
    private const MESSAGES_SAMPLE_ORDER_PO_EDIT_TITLE = 'sample_orders_messages_po_edited_title';
    private const MESSAGES_SAMPLE_ORDER_COMPLETE_TEXT = 'sample_orders_messages_delivery_confirmed';
    private const MESSAGES_SAMPLE_ORDER_COMPLETE_TITLE = 'sample_orders_messages_delivery_confirmed_title';
    private const MESSAGES_SAMPLE_ORDER_PO_CREATED_TEXT = 'sample_orders_messages_po_created';
    private const MESSAGES_SAMPLE_ORDER_PO_CREATED_TITLE = 'sample_orders_messages_po_created_title';
    private const MESSAGES_SAMPLE_ORDER_PO_CONFIRM_TEXT = 'sample_orders_messages_confirmed_po';
    private const MESSAGES_SAMPLE_ORDER_PO_CONFIRM_TITLE = 'sample_orders_messages_confirmed_po_title';
    private const MESSAGES_SAMPLE_ORDER_CONFIRM_PAYMENTS_TEXT = 'sample_orders_messages_confirmed_payments';
    private const MESSAGES_SAMPLE_ORDER_CONFIRM_PAYMENTS_TITLE = 'sample_orders_messages_confirmed_payments_title';
    private const MESSAGES_SAMPLE_ORDER_TRACKING_INFO_UPDATE_TEXT = 'sample_orders_messages_tracking_info_updated';
    private const MESSAGES_SAMPLE_ORDER_TRACKING_INFO_UPDATE_TITLE = 'sample_orders_messages_tracking_info_updated_title';
    private const MESSAGES_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE_TEXT = 'sample_orders_messages_delivery_address_changed';
    private const MESSAGES_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE_TITLE = 'sample_orders_messages_delivery_address_changed_title';

    private const NOTIFICATION_SAMPLE_ORDER_CREATE = 'sample_orders_created';
    private const NOTIFICATION_SAMPLE_ORDER_ASSIGN = 'sample_orders_assigned';
    private const NOTIFICATION_SAMPLE_ORDER_REQUEST = 'sample_orders_requested';
    private const NOTIFICATION_SAMPLE_ORDER_PO_EDIT = 'sample_orders_po_edited';
    private const NOTIFICATION_SAMPLE_ORDER_COMPLETE = 'sample_orders_completed';
    private const NOTIFICATION_SAMPLE_ORDER_PO_CREATE = 'sample_orders_po_created';
    private const NOTIFICATION_SAMPLE_ORDER_PO_CONFIRM = 'sample_orders_po_confirmed';
    private const NOTIFICATION_SAMPLE_ORDER_CREATE_FOR_BUYER = 'sample_order_created_for_buyer';
    private const NOTIFICATION_SAMPLE_ORDER_CONFIRM_PAYMENTS = 'sample_order_payments_confirmed';
    private const NOTIFICATION_SAMPLE_ORDER_TRACKING_INFO_UPDATE = 'sample_order_tracking_info_updated';
    private const NOTIFICATION_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE = 'sample_order_delivery_address_updated';

    /**
     * The bus messenger.
     */
    private MessengerInterface $busMessenger;

    /**
     * The models locator.
     */
    private ModelLocator $modelLocator;

    /**
     * The chat bindings service.
     */
    private ChatBindingService $chatBindings;

    /**
     * The internal adapter instance.
     */
    private ?ValidatorInterface $internalValidator;

    /**
     * The items repository.
     *
     * @var \User_Model
     */
    private $usersRepository;

    /**
     * The items repository.
     *
     * @var \Items_Model
     */
    private $itemsRepository;

    /**
     * The locations repository.
     *
     * @var \Country_Model
     */
    private $locationRepository;

    /**
     * The items snapshots repository.
     *
     * @var \Item_Snapshot_Model
     */
    private $snapshotRepository;

    /**
     * The companies repository.
     *
     * @var \Company_Model
     */
    private $companiesRepository;

    /**
     * The sample orders repository.
     *
     * @var \Sample_Orders_Model
     */
    private $sampleOrdersRepository;

    /**
     * Creates the instance of sample orders service.
     */
    public function __construct(
        ModelLocator $modelLocator,
        MessengerInterface $messenger,
        ChatBindingService $chatBindings,
        ?ValidatorInterface $internalValidator = null
    ) {
        $this->busMessenger = $messenger;
        $this->modelLocator = $modelLocator;
        $this->chatBindings = $chatBindings;
        $this->internalValidator = $internalValidator;
        $this->sampleOrdersRepository = $modelLocator->get(\Sample_Orders_Model::class);
        $this->locationRepository = $modelLocator->get(\Country_Model::class);
        $this->snapshotRepository = $modelLocator->get(\Item_Snapshot_Model::class);
        $this->companiesRepository = $modelLocator->get(\Company_Model::class);
        $this->usersRepository = $modelLocator->get(\User_Model::class);
        $this->itemsRepository = $modelLocator->get(\Items_Model::class);
    }

    /**
     * Returns information for sample.
     *
     * @throws NotFoundException     if sample order is not found
     * @throws OwnershipException    if sample order doesn't belong to the user
     * @throws AccessDeniedException if sample order is malformed
     */
    public function getSampleOrderInformation(
        ?int $sampleOrderId,
        ?int $userId = null,
        bool $appendBuyer = false,
        bool $appendSeller = false,
        bool $appendShipper = false,
        bool $appendBills = false
    ): array {
        //region Security
        if (null === $sampleOrderId) {
            throw new NotFoundException('The order ID must be provided', static::ORDER_NOT_FOUND_ERROR);
        }

        if (
            null === ($sampleOrder = $this->sampleOrdersRepository->findOneBy([
                'conditions' => ['order' => $sampleOrderId],
                'with'       => array_filter(
                    [
                        'status',
                        !$appendBuyer ? null : 'buyer',
                        !$appendSeller ? null : 'seller',
                        !$appendShipper ? null : 'shipper',
                        'bills' => !$appendBills ? null : function (RelationInterface $relation): void {
                            /** @var \User_Bills_Model $bills */
                            $bills = model(\User_Bills_Model::class);
                            $table = $relation->getRelated()->getTable();
                            $relation
                                ->getQuery()
                                ->leftJoin(
                                    $table,
                                    $bills->get_bills_types_table(),
                                    $bills->get_bills_types_table(),
                                    "{$bills->get_bills_types_table()}.{$bills->get_bills_types_table_primary_key()} = {$table}.id_type_bill"
                                )
                            ;
                        },
                    ]
                ),
            ]))
        ) {
            throw new NotFoundException("The sample order with ID '{$sampleOrderId}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        if (null !== $userId) {
            $buyerId = null !== $sampleOrder['id_buyer'] ? (int) $sampleOrder['id_buyer'] : null;
            $sellerId = null !== $sampleOrder['id_seller'] ? (int) $sampleOrder['id_seller'] : null;
            if ($userId !== $buyerId && $userId !== $sellerId) {
                throw new OwnershipException(
                    "The sample with ID '{$sampleOrderId}' doesn't belong to the user '{$userId}'.",
                    SampleServiceInterface::ORDER_OWNERSHIP_ERROR
                );
            }
        }
        //endregion Security

        //region Order parts
        try {
            $decode = fn ($value) => null === $value || \is_array($value) ? $value : json_decode($value, true, JSON_THROW_ON_ERROR);
            $sampleOrder['purchase_order'] = $decode($sampleOrder['purchase_order'] ?? null) ?? [];
            $sampleOrder['purchased_products'] = $decode($sampleOrder['purchased_products'] ?? null) ?? [];
            $sampleOrder['purchase_order_timeline'] = $decode($sampleOrder['purchase_order_timeline'] ?? null) ?? [];
        } catch (\Exception $exception) {
            throw new AccessDeniedException(
                'Access to sample order denied - sample order is malformed',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR,
                $exception
            );
        }

        $sampleOrder['price'] = \priceToUsdMoney($sampleOrder['price']);
        $sampleOrder['final_price'] = \priceToUsdMoney($sampleOrder['final_price']);
        //endregion Order parts

        return $sampleOrder;
    }

    /**
     * Request new sample order.
     *
     * @throws ValidationException if provided data is not valid
     */
    public function requestOrderFromItem(
        Request $request,
        PersonInterface $user,
        ?int $itemId,
        ParameterBag $messages,
        bool $providesCustomAddress = false
    ): array {
        //region Security
        //region Validation
        $violations = new ConstraintViolationList();
        $validator = $baseValidator = new RequestSampleOrderValidator($this->internalValidator);
        if ($providesCustomAddress) {
            $validator = new AggregateValidator([$baseValidator, new AddressValidator($this->internalValidator)]);
        }

        if (!$validator->validate($request->request->all())) {
            $violations->merge($validator->getViolations());
        }

        if ($violations->count() > 0) {
            throw new ValidationException('The order creation failed due to validation errors', 0, null, $violations);
        }
        //endregion Validation
        //endregion Security

        //region Order
        list($item, $snapshot) = $this->resolveItemRelatedComponents($itemId);

        //region Buyer information
        $buyer = $this->getBuyerInformation($buyerId = $user->getId());
        if (!$providesCustomAddress) {
            $buyerLocation = \arrayCamelizeAssocKeys($this->usersRepository->get_user_location($buyerId));
        } else {
            $buyerLocation = array_merge(
                \arrayCamelizeAssocKeys($this->locationRepository->get_precise_location(
                    $request->request->getInt('country'),
                    $request->request->getInt('state'),
                    $request->request->getInt('city'),
                )),
                [
                    'address'    => \decodeCleanInput(\cleanInput($request->request->get('address'))),
                    'postalCode' => \decodeCleanInput(\cleanInput($request->request->get('postal_code'))),
                ]
            );
        }
        //endregion Buyer information

        //region Seller information
        $sellerId = (int) $snapshot['idSeller'];
        $seller = $this->getSellerInformation($sellerId);
        //endregion Seller information

        $quantity = 1;
        $products = new ArrayCollection();
        $itemPrice = \priceToUsdMoney($snapshot['price'] ?? 0);
        $itemWeight = (float) ($snapshot['itemWeight'] ?? 0) * $quantity;
        $products->add(
            $firstProduct = $this->makeProductFromSnapshot($item, $snapshot, $quantity, $itemPrice)
        );

        $orderId = $this->addSampleOrder(
            $sellerId,
            $buyerId,
            null,
            $itemWeight,
            $itemPrice,
            $products,
            $timeline = new ArrayCollection([[
                'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
                'user'    => (string) $user->getGroupName(),
                'message' => (string) $messages->get('note') ?? null,
            ]]),
            $this->getOrderDepartureLocation($seller, $seller['location'] ?? []),
            $this->getOrderDestinationLocation($buyer, $buyerLocation ?? []),
            [$buyer, $seller],
            \decodeCleanInput(\cleanInput($request->request->get('description'), false, false))
        );
        //endregion Order

        //region Create chat room
        $chatService = new ChatService();
        $resourceType = ResourceType::from(ResourceType::SAMPLE_ORDER);
        $messageReplacements = [
            '[USER]'       => \cleanOutput((string) $user->getName()),
            '[ORDER]'      => \orderNumber($orderId),
            '[USER_LINK]'  => \getMyProfileLink(),
            '[ORDER_LINK]' => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
        ];
        $notificationReplacements = [
            '{{ORDER}}'       => \orderNumber($orderId),
            '[[ITEM-LINK]]'   => \sprintf('<a href="%s" target="_blank">', \makeItemUrl($firstProduct['item_id'], $firstProduct['name'])),
            '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
            '[[/ITEM-LINK]]'  => '</a>',
            '[[/ORDER-LINK]]' => '</a>',
        ];
        $this->busMessenger->bus('command.bus')->dispatch(new CreateDirectMatrixChatRoom(
            $chatService->makeSubjectForType($resourceType, $buyerId, $sellerId, $orderId),
            $buyerId,
            $sellerId,
            (new ResourceOptions())->type($resourceType)->id((string) $orderId ?: null)->attributes([
                'create.message_code'         => static::NOTIFICATION_SAMPLE_ORDER_REQUEST,
                'create.recipients'           => [$sellerId],
                'create.message_replacements' => $messageReplacements,
                'create.notification_subject' => translate(static::MESSAGES_SAMPLE_ORDER_REQUEST_TITLE, $notificationReplacements),
                'create.notification_content' => translate(static::MESSAGES_SAMPLE_ORDER_REQUEST_TEXT, $notificationReplacements),
            ])
        ));
        //endregion Create chat room

        return [
            'id'                    => $orderId,
            'idBuyer'               => $buyerId,
            'idSeller'              => $sellerId,
            'price'                 => $itemPrice,
            'buyer'                 => $buyer,
            'seller'                => $seller,
            'purchasedProducts'     => $products,
            'purchaseOrderTimeline' => $timeline,
        ];
    }

    /**
     * Creates the order sample for one item.
     *
     * @throws OwnershipException  if item doesn't belong to the user
     * @throws ValidationException if provided data is not valid
     */
    public function createOrderFromItem(
        Request $request,
        PersonInterface $user,
        ?int $itemId,
        ?int $buyerId,
        ?int $roomId,
        ParameterBag $messages
    ): array {
        //region Security
        //region Validation
        $violations = new ConstraintViolationList();
        $baseValidator = new SampleOrderValidator($this->internalValidator);
        $itemsValidator = new SampleOrderItemValidator($this->internalValidator);

        // If item is no an array, then we validate all data at onee with aggregate validator.
        $validator = new AggregateValidator([$baseValidator, $itemsValidator]);
        if (!$validator->validate($request->request->all())) {
            $violations->merge($validator->getViolations());
        }

        if ($violations->count() > 0) {
            throw new ValidationException('The order creation failed due to validation errors', 0, null, $violations);
        }
        //endregion Validation

        //region Item accessibility
        if (!$this->itemsRepository->my_item($user->getId(), $itemId)) {
            throw new OwnershipException('Seller can create sample orders only for his own items.', static::ITEM_OWNERSHIP_ERROR);
        }
        //endregion Item accessibility
        //endregion Security

        //region Order
        list($item, $snapshot) = $this->resolveItemRelatedComponents($itemId);
        $seller = $this->getSellerInformation($sellerId = $user->getId());
        $buyer = null;
        if (null !== $buyerId) {
            $buyer = $this->getBuyerInformation($buyerId);
            $buyer['location'] = \arrayCamelizeAssocKeys($this->usersRepository->get_user_location($buyerId));
        }

        $products = new ArrayCollection();
        $quantity = $request->request->getInt('quantity', 1);
        $itemPrice = \priceToUsdMoney($request->request->get('price', 0));
        $itemWeight = (float) ($snapshot['itemWeight'] ?? 0) * $quantity;
        $products->add(
            $this->makeProductFromSnapshot($item, $snapshot, $quantity, $itemPrice)
        );

        $orderId = $this->addSampleOrder(
            $sellerId,
            $buyerId,
            $roomId,
            $itemWeight,
            $itemPrice,
            $products,
            $timeline = new ArrayCollection([[
                'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
                'user'    => (string) $user->getGroupName(),
                'message' => (string) $messages->get('note') ?? null,
            ]]),
            $this->getOrderDepartureLocation($seller, $seller['location'] ?? []),
            null !== $buyer ? $this->getOrderDestinationLocation($buyer, $buyer['location'] ?? []) : null,
            array_filter([$seller, $buyer]),
            null,
            \decodeCleanInput(\cleanInput($request->request->get('description'), false, false)),
        );
        //endregion Order

        // Send notifications
        $this->sendNotifications(
            (int) $orderId,
            $sellerId,
            [$buyerId],
            static::NOTIFICATION_SAMPLE_ORDER_CREATE_FOR_BUYER,
            static::MESSAGES_SAMPLE_ORDER_CREATE_TITLE,
            static::MESSAGES_SAMPLE_ORDER_CREATE_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ITEM-LINK]]'   => \sprintf('<a href="%s" target="_blank">', \makeItemUrl($products[0]['item_id'], $products[0]['name'])),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ITEM-LINK]]'  => '</a>',
                '[[/ORDER-LINK]]' => '</a>',
            ],
            [$roomId]
        );

        return [
            'id'                    => $orderId,
            'idBuyer'               => $buyerId,
            'idSeller'              => $sellerId,
            'price'                 => $itemPrice,
            'buyer'                 => $buyer,
            'seller'                => $seller,
            'purchasedProducts'     => $products,
            'purchaseOrderTimeline' => $timeline,
        ];
    }

    /**
     * Creates sample order form multiple items.
     *
     * @throws OwnershipException  if at least one of the items doesn't belong to the user
     * @throws NotFoundException   if at least one of the items is not found
     * @throws ValidationException if provided data is not valid
     */
    public function createOrderForItems(
        Request $request,
        PersonInterface $user,
        array $items,
        ?int $buyerId,
        ?string $roomId,
        ParameterBag $messages
    ): array {
        //region Security
        //region Validation
        $violations = new ConstraintViolationList();
        $baseValidator = new SampleOrderValidator($this->internalValidator);
        $itemsValidator = new SampleOrderItemValidator($this->internalValidator);

        if (!$baseValidator->validate($request->request->all())) {
            $violations->merge($baseValidator->getViolations());
        }
        // If items are the array, then we need to validate all entries in that array separatelly
        // after main content validation. That is why sequence validator is used.
        if (!(new SequenceValidator($itemsValidator))->validate(array_values($items))) {
            $violations->merge($itemsValidator->getViolations());
        }

        if ($violations->count() > 0) {
            throw new ValidationException('The order creation failed due to validation errors', 0, null, $violations);
        }
        //endregion Validation

        //region Items accessibility
        if (!$this->itemsRepository->my_items($user->getId(), $itemsIds = array_column($items, 'id'))) {
            throw new OwnershipException("At least one of the items doesn't belong to this user.", static::ITEMS_OWNERSHIP_ERROR);
        }
        //endregion Items accessibility
        //endregion Security

        //region Order
        $seller = $this->getSellerInformation($sellerId = $user->getId());
        $buyer = null;
        if (null !== $buyerId) {
            $buyer = $this->getBuyerInformation($buyerId);
            $buyer['location'] = \arrayCamelizeAssocKeys($this->usersRepository->get_user_location($buyerId));
        }

        $products = new ArrayCollection();
        $itemPairs = $this->resolveItemComponentsPairs($itemsIds);
        $orderPrice = Money::USD(0);
        $orderWeight = 0;
        $purchasedItems = arrayByKey($items, 'id');

        //region Products
        foreach ($itemPairs as $itemId => list($item, $snapshot)) {
            if (null === ($purchasedItem = $purchasedItems[$itemId] ?? null)) {
                throw new NotFoundException('At least one of the items is not found', static::ITEM_NOT_FOUND_ERROR);
            }

            $quantity = (int) ($purchasedItem['quantity'] ?? 1);
            $itemPrice = \priceToUsdMoney($purchasedItem['price'] ?? 0);
            $itemWeight = (float) ($snapshot['itemWeight'] ?? 0) * $quantity;
            $products->add(
                $this->makeProductFromSnapshot($item, $snapshot, $quantity, $itemPrice)
            );

            $orderWeight += $itemWeight;
            $orderPrice = $orderPrice->add($itemPrice);
        }
        //endregion Products

        $orderId = $this->addSampleOrder(
            $sellerId,
            $buyerId,
            $roomId,
            $orderWeight,
            $orderPrice,
            $products,
            $timeline = new ArrayCollection([[
                'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
                'user'    => (string) $user->getGroupName(),
                'message' => (string) $messages->get('note') ?? null,
            ]]),
            $this->getOrderDepartureLocation($seller, $seller['location'] ?? []),
            null !== $buyer ? $this->getOrderDestinationLocation($buyer, $buyer['location'] ?? []) : null,
            array_filter([$seller, $buyer]),
            null,
            \decodeCleanInput(\cleanInput($request->request->get('description'), false, false)),
        );
        //endregion Order

        // Send notifications
        $this->sendNotifications(
            (int) $orderId,
            $sellerId,
            [$buyerId],
            static::NOTIFICATION_SAMPLE_ORDER_CREATE_FOR_BUYER,
            static::MESSAGES_SAMPLE_ORDER_CREATE_TITLE,
            static::MESSAGES_SAMPLE_ORDER_CREATE_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ITEM-LINK]]'   => \sprintf('<a href="%s" target="_blank">', \makeItemUrl($products[0]['item_id'], $products[0]['name'])),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ITEM-LINK]]'  => '</a>',
                '[[/ORDER-LINK]]' => '</a>',
            ],
            [$roomId]
        );

        return [
            'id'                    => $orderId,
            'idBuyer'               => $buyerId,
            'idSeller'              => $sellerId,
            'price'                 => $orderPrice,
            'buyer'                 => $buyer,
            'seller'                => $seller,
            'purchasedProducts'     => $products,
            'purchaseOrderTimeline' => $timeline,
        ];
    }

    /**
     * Assign sample order to buyer.
     */
    public function assignOrder(
        int $orderId,
        ?ParameterBag $sampleOrder,
        PersonInterface $user,
        int $buyerId,
        string $roomId,
        ParameterBag $messages
    ): void {
        //region Sample Order resolution
        if (null === $sampleOrder) {
            $sampleOrder = new ParameterBag(\arrayCamelizeAssocKeys($this->getSampleOrderInformation($orderId, $user->getId())));
        }
        if ($user->getId() !== $sampleOrder->getInt('idSeller')) {
            throw new AccessDeniedException('Only seller can assign Sample Order.', static::ORDER_ACCESS_DENIED_ERROR);
        }
        // Check if this sample order is already assigned to the chat room.
        $resourceOptions = ResourceOptions::fromRaw(ResourceType::from(ResourceType::SAMPLE_ORDER), (string) $orderId);
        if ($this->chatBindings->hasRoomBindings($resourceOptions, $user->getId(), $buyerId, $roomId)) {
            throw new DependencyException('This sample order is already assigned to the room.', static::ALREADY_ASSIGNED_ORDER_ERROR);
        }
        //endregion Sample Order resolution

        //region PO destination update
        //region Buyer location
        $buyer = $this->getBuyerInformation($buyerId);
        $buyerLocation = \arrayCamelizeAssocKeys($this->usersRepository->get_user_location($buyerId));
        //endregion Buyer location

        $products = $sampleOrder->get('purchasedProducts') ?? [];
        $purchaseOrder = $sampleOrder->get('purchaseOrder');
        $destinationInformation = $this->getOrderDestinationLocation($buyer, $buyerLocation);
        $purchaseOrderDestination = [
            'full_address' => $destinationInformation->get('fullAddress') ?? null,
            'address'      => $destinationInformation->get('address') ?? null,
            'country'      => $destinationInformation->get('countryId') ?? null,
            'state'        => $destinationInformation->get('stateId') ?? null,
            'city'         => $destinationInformation->get('cityId') ?? null,
            'zip'          => $destinationInformation->get('postalCode') ?? null,
        ];

        $purchaseOrder['is_deliverable'] = count($purchaseOrderDestination) === count(array_filter($purchaseOrderDestination));
        $purchaseOrder['shipping_to'] = $purchaseOrderDestination;
        //endregion PO destination update

        //region Timeline
        $orderTimeline = $sampleOrder->get('purchaseOrderTimeline') ?? [];
        $orderTimeline = $orderTimeline instanceof Collection ? $orderTimeline : new ArrayCollection($orderTimeline);
        $orderTimeline->add([
            'user'    => (string) $user->getGroupName(),
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'message' => $messages->get('note') ?? null,
        ]);
        //endregion Timeline

        //region Assign
        // In order to properly assign the sample order to the room we first need to
        // update sample order information.
        $this->updateSampleOrder($orderId, [
            'id_buyer'                => $buyerId,
            'id_theme'                => null,
            'ship_to'                 => $destinationInformation->get('fullAddress') ?? null,
            'ship_to_zip'             => $destinationInformation->get('postalCode') ?? null,
            'ship_to_city'            => $destinationInformation->get('cityId') ?? null,
            'ship_to_state'           => $destinationInformation->get('stateId') ?? null,
            'ship_to_country'         => $destinationInformation->get('countryId') ?? null,
            'ship_to_address'         => $destinationInformation->get('address') ?? null,
            'purchase_order'          => $purchaseOrder,
            'purchase_order_timeline' => $orderTimeline->getValues(),
        ]);
        // After that let's bind the resource to the sample order
        $this->chatBindings->bindResourceToRoom($resourceOptions, $roomId, $user->getId(), $buyerId);
        //endregion Assign

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            [$buyerId],
            static::NOTIFICATION_SAMPLE_ORDER_ASSIGN,
            static::MESSAGES_SAMPLE_ORDER_ASSIGN_TITLE,
            static::MESSAGES_SAMPLE_ORDER_ASSIGN_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ITEM-LINK]]'   => \sprintf('<a href="%s" target="_blank">', \makeItemUrl($products[0]['item_id'], $products[0]['name'])),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ITEM-LINK]]'  => '</a>',
                '[[/ORDER-LINK]]' => '</a>',
            ],
            [$roomId]
        );
        //endregion Notifications
    }

    /**
     * Set delivery address.
     *
     * @throws NotFoundException                  if sample order not found or if sample order theme doesn't contain any messages
     * @throws ValidationException                if provided data is not valid
     * @throws OwnershipException                 if order doesn\'t belong to user
     * @throws QueryException                     if failed to update order
     * @throws PurchaseOrderConfirmationException if PO is already confirmed
     * @throws InvalidStatusException             if sample order status is invalid
     */
    public function setDeliveryAddress(
        Request $request,
        ParameterBag $order,
        PersonInterface $user,
        ParameterBag $messages,
        ?array $allowedStatuses,
        bool $providesCustomAddress = false
    ): void {
        //region Security
        //region Validation
        $violations = new ConstraintViolationList();

        if ($providesCustomAddress) {
            $validator = new AddressValidator($this->internalValidator);

            if (!$validator->validate($request->request->all())) {
                $violations->merge($validator->getViolations());
            }

            if ($violations->count() > 0) {
                throw new ValidationException('The order creation failed due to validation errors', 0, null, $violations);
            }
        }
        //endregion Validation

        // Check access for statuses
        if (null !== $allowedStatuses && !in_array($order->get('status')['alias'] ?? null, $allowedStatuses)) {
            throw new InvalidStatusException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if user is buyer
        if ($user->getId() !== (int) $order->get('idBuyer')) {
            throw new AccessDeniedException('Only buyer can change delivery address', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if PO is not confirmed yet.
        if ($order->get('purchaseOrder')['is_confirmed'] ?? false) {
            throw new PurchaseOrderConfirmationException(
                'The delivery address cannot be updated for if PO is already confirmed.',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR
            );
        }
        //endregion Security

        $buyer = $this->getBuyerInformation($user->getId());
        if (!$providesCustomAddress) {
            $userLocation = \arrayCamelizeAssocKeys($this->usersRepository->get_user_location($user->getId()));
        } else {
            $userLocation = array_merge(
                \arrayCamelizeAssocKeys($this->locationRepository->get_precise_location(
                    $request->request->getInt('country'),
                    $request->request->getInt('state'),
                    $request->request->getInt('city'),
                )),
                [
                    'address'    => \decodeCleanInput(\cleanInput($request->request->get('address'))),
                    'postalCode' => \decodeCleanInput(\cleanInput($request->request->get('postal_code'))),
                ]
            );
        }

        $purchaseOrderTimeline = $order->get('purchaseOrderTimeline');
        $purchaseOrderTimeline[] = [
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'user'    => (string) $user->getGroupName(),
            'message' => (string) $messages->get('note') ?? null,
        ];

        $purchaseOrder = $order->get('purchaseOrder');
        $destinationLocation = $this->getOrderDestinationLocation($buyer, $userLocation);
        $purchaseOrder['is_deliverable'] = true;
        $purchaseOrder['is_confirmable'] = false;
        $purchaseOrder['shipping_to'] = [
            'full_address'  => $destinationLocation->get('fullAddress') ?? null,
            'address'       => $destinationLocation->get('address') ?? null,
            'country'       => $destinationLocation->get('countryId') ?? null,
            'state'         => $destinationLocation->get('stateId') ?? null,
            'city'          => $destinationLocation->get('cityId') ?? null,
            'zip'           => $destinationLocation->get('postalCode') ?? null,
        ];

        $this->updateSampleOrder($orderId = $order->getInt('id'), [
            'ship_to'                   => $destinationLocation->get('fullAddress') ?? null,
            'ship_to_zip'               => $destinationLocation->get('postalCode') ?? null,
            'ship_to_city'              => $destinationLocation->get('cityId') ?? null,
            'ship_to_state'             => $destinationLocation->get('stateId') ?? null,
            'ship_to_country'           => $destinationLocation->get('countryId') ?? null,
            'ship_to_address'           => $destinationLocation->get('address') ?? null,
            'purchase_order'            => $purchaseOrder,
            'purchase_order_timeline'   => $purchaseOrderTimeline,
        ]);

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            [$order->getInt('idSeller')],
            static::NOTIFICATION_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE,
            static::MESSAGES_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE_TITLE,
            static::MESSAGES_SAMPLE_ORDER_DELIVERY_ADDRESS_UPDATE_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Edit tracking info.
     *
     * @throws ValidationException   if provided data is not valid
     * @throws NotFoundException     if sample order not found
     * @throws AccessDeniedException if the current user is not a seller of this sample order
     * @throws QueryException        if failed to update order
     */
    public function editTrackingInfo(
        Request $request,
        int $orderId,
        ?ParameterBag $sampleOrder,
        PersonInterface $user,
        ParameterBag $messages
    ): void {
        //region Sample Order resolution
        if (null === $sampleOrder) {
            $sampleOrder = $this->getSampleOrderInformation($orderId, $user->getId());
            if (null === $sampleOrder) {
                throw new NotFoundException("The order with id '{$orderId}' not found", static::ORDER_NOT_FOUND_ERROR);
            }

            $sampleOrder = new ParameterBag(\arrayCamelizeAssocKeys($sampleOrder));
        }

        if ($user->getId() !== (int) $sampleOrder->get('idSeller')) {
            throw new AccessDeniedException('You have no access to edit the order tracking info', static::ORDER_OWNERSHIP_ERROR);
        }
        //endregion Sample Order resolution

        //region Validation
        $violations = new ConstraintViolationList();
        $validator = $baseValidator = new SampleOrderTrackingInfoValidator($this->internalValidator);
        if (null === ($deliveryDate = $sampleOrder->get('deliveryDate'))) {
            $validator = new AggregateValidator([$baseValidator, new SampleOrderDeliveryDateValidator($this->internalValidator)]);
        }
        if (!$validator->validate($request->request->all())) {
            $violations->merge($validator->getViolations());
        }

        if ($violations->count() > 0) {
            throw new ValidationException('Failed to update tracking info due to validation errors', 0, null, $violations);
        }
        //endregion Validation

        //region Timeline
        $orderTimeline = $sampleOrder->get('purchaseOrderTimeline') ?? [];
        $orderTimeline = $orderTimeline instanceof Collection ? $orderTimeline : new ArrayCollection($orderTimeline);
        $orderTimeline->add([
            'user'    => (string) $user->getGroupName(),
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'message' => $messages->get('note') ?? null,
        ]);
        //endregion Timeline

        //region Update
        $orderUpdates = [
            'tracking_info'           => \decodeCleanInput(\cleanInput($request->request->get('track_info'), false, false)),
            'purchase_order_timeline' => $orderTimeline->getValues(),
        ];
        if (null === $deliveryDate) {
            $orderUpdates['delivery_date'] = \DateTimeImmutable::createFromFormat('m/d/Y', $request->request->get('delivery_date'));
        }

        $this->updateSampleOrder($orderId, $orderUpdates);
        //endregion Update

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            [$sampleOrder->getInt('idBuyer')],
            static::NOTIFICATION_SAMPLE_ORDER_TRACKING_INFO_UPDATE,
            static::MESSAGES_SAMPLE_ORDER_TRACKING_INFO_UPDATE_TITLE,
            static::MESSAGES_SAMPLE_ORDER_TRACKING_INFO_UPDATE_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Confirm order payment.
     *
     * @throws NotFoundException if sample_order is not found
     * @throws NotFoundException if sample_order doesn't contains bills
     */
    public function confirmOrderPayment(int $orderId, ?ParameterBag $sampleOrder, int $newStatus, ParameterBag $messages): void
    {
        if (null === $sampleOrder) {
            $sampleOrder = $this->getSampleOrderInformation($orderId, null, false, false, false, true);
            if (null === $sampleOrder) {
                throw new NotFoundException("The order with id '{$orderId}' not found", static::ORDER_NOT_FOUND_ERROR);
            }

            $sampleOrder = new ParameterBag(\arrayCamelizeAssocKeys($sampleOrder));
        }

        $orderBills = $sampleOrder->get('bills') ?? [];
        if (empty($orderBills)) {
            throw new NotFoundException('This sample order doesn\'t contain bills', static::BILLS_NOT_FOUND_ERROR);
        }

        $hasConfirmedBills = $hasNotConfirmedBills = false;
        foreach ($orderBills as $bill) {
            if (in_array($bill['status'], ['init', 'paid'])) {
                $hasNotConfirmedBills = true;

                break;
            }

            if ('confirmed' === $bill['status']) {
                $hasConfirmedBills = true;
            }
        }

        if ($hasNotConfirmedBills || !$hasConfirmedBills) {
            throw new DependencyException('Please confirm all bill payments before confirming the complete order payment.', static::CONFIRM_PAYMENT_ORDER_ERROR);
        }

        // UPDATE ORDER LOG
        $orderTimeline = $sampleOrder->get('purchaseOrderTimeline') ?? [];
        $orderTimeline = $orderTimeline instanceof Collection ? $orderTimeline : new ArrayCollection($orderTimeline);
        $orderTimeline->add([
            'user'    => 'EP Manager',
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'message' => $messages->get('note') ?? null,
        ]);

        $this->updateSampleOrder($orderId, [
            'id_status'               => $newStatus,
            'purchase_order_timeline' => $orderTimeline->getValues(),
        ]);

        //region Notifications
        $buyerId = $sampleOrder->getInt('idBuyer');
        $sellerId = $sampleOrder->getInt('idSeller');

        $this->sendNotifications(
            $orderId,
            $sellerId,
            [$buyerId, $sellerId],
            static::NOTIFICATION_SAMPLE_ORDER_CONFIRM_PAYMENTS,
            static::MESSAGES_SAMPLE_ORDER_CONFIRM_PAYMENTS_TITLE,
            static::MESSAGES_SAMPLE_ORDER_CONFIRM_PAYMENTS_TEXT,
            [
                '[LINK]'          => \getUrlForGroup('/sample_orders/my'),
                '[ORDER]'         => \orderNumber($orderId),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Edits the sample order purchase order (PO).
     *
     * @throws NotFoundException                  if one or more items were not found
     * @throws ValidationException                if failed validation
     * @throws AccessDeniedException              if purchase order was malformed
     * @throws QueryException                     if failed to update record in repository
     * @throws InvalidStatusException             if sample order status is invalid
     * @throws PurchaseOrderConfirmationException if PO is already confirmed
     */
    public function editPurchaseOrder(
        int $orderId,
        array $sampleOrder,
        array &$purchaseOrder,
        array &$purchasedProducts,
        ArrayCollection $purchaseOrderTimeline,
        Request $request,
        PersonInterface $user,
        array $changedProducts,
        ?array $allowedStatuses,
        ParameterBag $messages
    ): void {
        //region Security
        //region Validation
        $violations = new ConstraintViolationList();
        $baseValidator = new SimplifiedPurchaseOrderValidator($this->internalValidator);
        $productsValidator = new SampleOrderItemValidator($this->internalValidator);

        // First is the basic validation
        if (!$baseValidator->validate($request->request->all())) {
            $violations->merge($baseValidator->getViolations());
        }
        // Next we need to validate all entries in that array separatelly
        // after main content validation. That is why sequence validator is used.
        if (!(new SequenceValidator($productsValidator))->validate(array_values($changedProducts))) {
            $violations->merge($productsValidator->getViolations());
        }

        if ($violations->count() > 0) {
            throw new ValidationException('The order creation failed due to validation errors', 0, null, $violations);
        }
        //endregion Validation

        // Deny access for any status except new.
        if (null !== $allowedStatuses && !in_array($sampleOrder['status']['alias'] ?? null, $allowedStatuses)) {
            throw new InvalidStatusException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Stop if PO is already confirmed.
        if ($purchaseOrder['is_confirmed'] ?? false) {
            throw new PurchaseOrderConfirmationException(
                'The PO cannot be updated for if it already confirmed.',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR
            );
        }
        //endregion Security

        //region Order update
        $isFirstEdit = false === $purchaseOrder['is_edited'] ?? false;
        $buyerId = isset($sampleOrder['idBuyer']) ? (int) $sampleOrder['idBuyer'] : null;
        $dueDate = \DateTimeImmutable::createFromFormat('m/d/Y', $request->request->get('due_date'));
        $issueDate = new \DateTimeImmutable();
        $shipperId = $request->request->getInt('shipper');
        $description = \decodeCleanInput(\cleanInput($request->request->get('notes'), false, false));
        $searchTokens = \array_filter(\array_map('trim', \explode(',', $sampleOrder['searchTokens'] ?? '')));
        $originalPrice = \moneyToDecimal(\priceToUsdMoney($sampleOrder['finalPrice'] ?? 0));
        $purchaseOrderNumber = \decodeCleanInput(\cleanInput($request->request->get('number')));
        $purchaseOrderTimeline->add([
            'date'    => $issueDate->format(DATE_ATOM),
            'user'    => (string) $user->getGroupName(),
            'message' => (string) $messages->get('note') ?? null,
        ]);

        //region Update products
        $orderPrice = Money::USD(0);
        foreach (\arrayByKey($changedProducts, 'id') as $productId => $product) {
            if (!isset($purchasedProducts[$productId])) {
                throw new NotFoundException('At least one of the items has invalid ID value.', static::ITEM_NOT_FOUND_ERROR);
            }

            $quantity = (int) ($product['quantity'] ?? 1);
            $productPrice = \priceToUsdMoney($product['price'] ?? 0);
            $knownProduct = &$purchasedProducts[$productId];
            $knownProduct['quantity'] = $quantity;
            $knownProduct['total_price'] = \moneyToDecimal($productPrice);
            $orderPrice = $orderPrice->add($productPrice);
        }
        //endregion Update products

        //region Update search tokens
        $tokenPosition = array_search($originalPrice, $searchTokens);
        if (null !== $tokenPosition) {
            if (false !== $tokenPosition) {
                $searchTokens = array_replace($searchTokens, [$tokenPosition => moneyToDecimal($orderPrice)]);
            } else {
                $searchTokens[] = moneyToDecimal($orderPrice);
            }
        }
        //endregion Update search tokens

        //region Update
        $purchaseOrder['number'] = $purchaseOrderNumber;
        $purchaseOrder['invoice']['notes'] = $description;
        $purchaseOrder['invoice']['issue_date'] = $issueDate->format(DATE_ATOM);
        $purchaseOrder['invoice']['due_date'] = $dueDate->format(DATE_ATOM);
        $purchaseOrder['is_confirmable'] = true;
        $purchaseOrder['is_confirmed'] = false;
        $purchaseOrder['is_edited'] = true;

        $update = [
            'price'                   => \moneyToDecimal($orderPrice),
            'final_price'             => \moneyToDecimal($orderPrice),
            'id_shipper'              => $shipperId,
            'purchase_order'          => $purchaseOrder,
            'purchased_products'      => array_values($purchasedProducts),
            'purchase_order_timeline' => $purchaseOrderTimeline->getValues(),
        ];
        if (null !== $searchTokens) {
            $update['search_tokens'] = \implode(', ', \array_filter($searchTokens));
        }

        $this->updateSampleOrder($orderId, $update);
        //endregion Update
        //endregion Order update

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            null !== $buyerId ? [$buyerId] : [],
            $isFirstEdit ? static::NOTIFICATION_SAMPLE_ORDER_PO_CREATE : static::NOTIFICATION_SAMPLE_ORDER_PO_EDIT,
            $isFirstEdit ? static::MESSAGES_SAMPLE_ORDER_PO_CREATED_TITLE : static::MESSAGES_SAMPLE_ORDER_PO_EDIT_TITLE,
            $isFirstEdit ? static::MESSAGES_SAMPLE_ORDER_PO_CREATED_TEXT : static::MESSAGES_SAMPLE_ORDER_PO_EDIT_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Confirms the purchase order (PO).
     *
     * @throws AccessDeniedException              if sample order is malformed
     * @throws AccessDeniedException              if sample order status is invalid
     * @throws InvalidStatusException             if sample order status is invalid
     * @throws PurchaseOrderConfirmationException if sample order is already confirmed
     * @throws PurchaseOrderConfirmationException if sample order cannot be confirmed
     */
    public function confirmPurchaseOrder(
        int $orderId,
        array $sampleOrder,
        array &$purchaseOrder,
        ArrayCollection $purchaseOrderTimeline,
        PersonInterface $user,
        int $newStatusId,
        ?array $allowedStatuses,
        ParameterBag $messages
    ): void {
        //region Security
        // Check if status is valid. If sample order has invalid status we must deny access to this action.
        if (null !== $allowedStatuses && !in_array($sampleOrder['status']['alias'] ?? null, $allowedStatuses)) {
            throw new InvalidStatusException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if user is buyer
        if ($user->getId() !== (int) $sampleOrder['idBuyer']) {
            throw new AccessDeniedException('Only buyer can change delivery address.', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if PO is not confirmed yet.
        if ($purchaseOrder['is_confirmed'] ?? false) {
            throw new PurchaseOrderConfirmationException('The PO cannot be confirmed again.', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }
        // Check if PO CAN be confirmed
        if (!($purchaseOrder['is_confirmable'] ?? false && $purchaseOrder['is_deliverable'] ?? false)) {
            throw new PurchaseOrderConfirmationException('The PO cannot be confirmed right now.', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }
        //endregion Security

        //region Update
        $sellerId = isset($sampleOrder['idSeller']) ? (int) $sampleOrder['idSeller'] : null;
        //region Update timeline
        $purchaseOrderTimeline->add([
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'user'    => (string) $user->getGroupName(),
            'message' => (string) $messages->get('note') ?? null,
        ]);
        //endregion Update timeline

        //region Update PO
        $purchaseOrder['is_confirmable'] = false;
        $purchaseOrder['is_confirmed'] = true;
        $purchaseOrder['contract']['id'] = Uuid::uuid5(GENERIC_UUID_NAMESPACE, sprintf('contract_%s', orderNumberOnly($orderId)));
        $purchaseOrder['invoice']['id'] = Uuid::uuid5(GENERIC_UUID_NAMESPACE, sprintf('invoice_%s', orderNumberOnly($orderId)));
        //endregion Update PO

        $this->updateSampleOrder($orderId, [
            'id_status'               => $newStatusId,
            'purchase_order'          => $purchaseOrder,
            'purchase_order_timeline' => $purchaseOrderTimeline->getValues(),
        ]);

        //region Attach files
        // Add sample order files
        $commandBus = $this->busMessenger->bus('command.bus');
        // Get room id
        try {
            $resourceOptions = ResourceOptions::fromRaw(ResourceType::from(ResourceType::SAMPLE_ORDER), (string) $orderId);
            $roomId = $this->chatBindings->getRoomBindings($resourceOptions, $sellerId, $buyerId = $user->getId())['room']['room_id'];
        } catch (\Throwable $e) {
            $roomId = null;
        }
        $invoiceMessage = new AddSampleOrderDocument(
            $orderId,
            $sellerId,
            $buyerId,
            'invoice',
            \translate('sample_orders_documents_invoice_title', ['{{ORDER}}' => \orderNumber($orderId)]),
            \translate('sample_orders_timeline_notes_invoice_created'),
            $roomId
        );
        $contractMessage = new AddSampleOrderDocument(
            $orderId,
            $sellerId,
            $buyerId,
            'contract',
            \translate('sample_orders_documents_contract_title', ['{{ORDER}}' => \orderNumber($orderId)]),
            \translate('sample_orders_timeline_notes_contract_created'),
            $roomId
        );

        // Dispatch messages
        $commandBus->dispatch($invoiceMessage, [new DelayStamp(1000), new AmqpStamp('order.sample.documents')]);
        $commandBus->dispatch($contractMessage, [new DelayStamp(2000), new AmqpStamp('order.sample.documents')]);
        //endregion Attach files
        //endregion Update

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            [$sellerId],
            static::NOTIFICATION_SAMPLE_ORDER_PO_CONFIRM,
            static::MESSAGES_SAMPLE_ORDER_PO_CONFIRM_TITLE,
            static::MESSAGES_SAMPLE_ORDER_PO_CONFIRM_TEXT,
            [
                '[USER]'          => \cleanOutput((string) $user->getName()),
                '[ORDER]'         => \orderNumber($orderId),
                '[USER_LINK]'     => \getMyProfileLink(),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Confirms the sample order delivery.
     *
     * @throws AccessDeniedException if user is not buyer
     * @throws DeliveryException     if sample order delivery date is NULL
     */
    public function confirmOrderDelivery(
        int $orderId,
        ?ParameterBag $sampleOrder,
        PersonInterface $user,
        ParameterBag $messages,
        int $newStatusId
    ): void {
        //region Sample Order resolution
        if (null === $sampleOrder) {
            $sampleOrder = new ParameterBag(\arrayCamelizeAssocKeys(
                $this->getSampleOrderInformation($orderId, $user->getId())
            ));
        }

        // Check if user is buyer.
        if ($user->getId() !== $sampleOrder->getInt('idBuyer')) {
            throw new AccessDeniedException('Only buyer can confirm Sample Order delivery.', static::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if delivery date is set.
        if (null === $sampleOrder->get('deliveryDate')) {
            throw new DeliveryException('The delivery of the Sample Order cannot be confirmed when delivery date is NULL.', static::ORDER_ACCESS_DENIED_ERROR);
        }
        //endregion Sample Order resolution

        //region Timeline
        $orderTimeline = $sampleOrder->get('purchaseOrderTimeline') ?? [];
        $orderTimeline = $orderTimeline instanceof Collection ? $orderTimeline : new ArrayCollection($orderTimeline);
        $orderTimeline->add([
            'user'    => (string) $user->getGroupName(),
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'message' => $messages->get('note') ?? null,
        ]);
        //endregion Timeline

        //region Update
        $this->updateSampleOrder($orderId, [
            'id_status'               => $newStatusId,
            'pickup_date'             => new \DateTimeImmutable(),
            'purchase_order_timeline' => $orderTimeline->getValues(),
        ]);
        //endregion Update

        //region Notifications
        $this->sendNotifications(
            $orderId,
            $user->getId(),
            [$sampleOrder->getInt('idSeller')],
            static::NOTIFICATION_SAMPLE_ORDER_COMPLETE,
            static::MESSAGES_SAMPLE_ORDER_COMPLETE_TITLE,
            static::MESSAGES_SAMPLE_ORDER_COMPLETE_TEXT,
            [
                '[LINK]'          => \getUrlForGroup('/sample_orders/my'),
                '[ORDER]'         => \orderNumber($orderId),
                '[ORDER_LINK]'    => \getUrlForGroup("/sample_orders/my/order/{$orderId}"),
                '{{ORDER}}'       => \orderNumber($orderId),
                '[[ORDER-LINK]]'  => \sprintf('<a href="%s" target="_blank">', \getUrlForGroup("/sample_orders/my/order/{$orderId}")),
                '[[/ORDER-LINK]]' => '</a>',
            ],
        );
        //endregion Notifications
    }

    /**
     * Updates the sample order.
     *
     * @throws QueryException if failed to update record
     */
    public function updateOrder(int $orderId, array $sampleOrder, bool $transactional = false): void
    {
        $connection = $this->sampleOrdersRepository->getConnection();
        if ($transactional) {
            $connection->beginTransaction();
        }

        try {
            if (!$this->sampleOrdersRepository->updateOne($orderId, $sampleOrder)) {
                throw new QueryException($this->sampleOrdersRepository->getHandler(), null, 'The execution of the query failed', static::STORAGE_UPDATE_ERROR);
            }
            if ($transactional) {
                $connection->commit();
            }
        } catch (\Throwable $e) {
            if ($transactional) {
                $connection->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Returns item-related components (item, snapshot etc.).
     *
     * @throws AccessDeniedException if item is not accessible for ordering
     * @throws NotFoundException     if item is not found
     * @throws NotFoundException     if snapshot is not found
     */
    private function resolveItemRelatedComponents(int $itemId): array
    {
        //region Item
        if (null === $itemId || null === ($item = $this->itemsRepository->get_item($itemId))) {
            throw new NotFoundException(
                null === $itemId ? 'The item ID is expected' : "The item with ID '{$itemId}' is not found.",
                static::ITEM_NOT_FOUND_ERROR
            );
        }

        if (!(bool) (int) ($item['visible'] ?? 0) || (bool) (int) ($item['blocked'])) {
            throw new AccessDeniedException('The access to the not visible or blocked items is denied.', static::ITEM_ACCESS_DENIED_ERROR);
        }
        //endregion Item

        //region Snapshot
        if (null === ($snapshot = $this->snapshotRepository->get_last_item_snapshot($itemId))) {
            throw new NotFoundException("The snapshot for item '{$itemId}' is not found", static::SNAPSHOT_NOT_FOUND_ERROR);
        }
        //endregion Snapshot

        return [
            \arrayCamelizeAssocKeys($item),
            \arrayCamelizeAssocKeys($snapshot),
        ];
    }

    /**
     * Returns the item-snapshot pairs for every provided item ID.
     *
     * @throws NotFoundException     if no items provided or items are not found or at least one of the items is not found
     * @throws NotFoundException     if at least one snapshot for the items is not found
     * @throws AccessDeniedException if at least one of the items is not accessible
     *
     * @return \Generator<array,array>
     */
    private function resolveItemComponentsPairs(array $itemsIds): \Generator
    {
        //region Items
        if (
            empty($itemsIds)
            || empty($items = $this->itemsRepository->get_items(['list_item' => implode(', ', $itemsIds)]))
            || count($itemsIds) !== count($items)
        ) {
            throw new NotFoundException(
                empty($itemsIds) ? 'At least one items is required' : 'At least one item from the list is not found.',
                static::ITEMS_NOT_FOUND_ERROR
            );
        }

        foreach ($items as $item) {
            if (!(bool) (int) ($item['visible'] ?? 0) || (bool) (int) ($item['blocked'])) {
                throw new AccessDeniedException('At least one of the items cannot be accessed.', static::ITEM_ACCESS_DENIED_ERROR);
            }
        }
        //endregion Items

        //region Snapshots
        if (
            empty($snapshots = $this->snapshotRepository->get_latest_snapshots_for_items($itemsIds))
            || count($snapshots) !== count($items)
        ) {
            throw new NotFoundException('At least one of the is not found.', static::SNAPSHOT_NOT_FOUND_ERROR);
        }
        //endregion Snapshots

        $items = arrayByKey($items, 'id');
        $snapshots = arrayByKey($snapshots, 'id_item');
        foreach ($items as $itemId => $item) {
            if (!isset($snapshots[$itemId])) {
                throw new NotFoundException('At least one of the is not found.', static::SNAPSHOT_NOT_FOUND_ERROR);
            }

            yield $itemId => [\arrayCamelizeAssocKeys($item), \arrayCamelizeAssocKeys($snapshots[$itemId])];
        }
    }

    /**
     * Returns information about seller.
     *
     * @throws NotFoundException if seller's company is not found
     */
    private function getSellerInformation(int $userId): array
    {
        try {
            $seller = $this->companiesRepository->get_seller_base_company(
                $userId,
                <<<'COLUMNS'
                cb.id_company as company_id, cb.name_company as company, cb.address_company as address, cb.zip_company as postalCode,
                u.fname as firstname, u.lname as lastname, TRIM(CONCAT(u.fname, ' ', u.lname)) as fullname
                COLUMNS,
                true
            );
        } catch (\Exception $exception) {
            $seller = null;
        }

        if (null === $seller) {
            throw new NotFoundException(
                "The company of the seller with ID '{$userId}' is not found",
                static::USER_NOT_FOUND_ERROR,
                null === $exception ? null : LegacyQueryException::executionFailed($this->companiesRepository->db, $exception, static::STORAGE_WRITE_ERROR)
            );
        }

        return array_merge(
            $seller,
            ['location' => \arrayCamelizeAssocKeys($this->companiesRepository->get_company_location((int) $seller['company_id']))]
        );
    }

    /**
     * Returns information about buyer.
     *
     * @throws NotFoundException if buyer is not found
     */
    private function getBuyerInformation(int $userId): array
    {
        try {
            $buyer = $this->usersRepository->getSimpleUser(
                $userId,
                <<<'COLUMNS'
                users.fname as firstname, users.lname as lastname, TRIM(CONCAT(users.fname, ' ', users.lname)) as fullname,
                users.address, users.zip as postalCode
                COLUMNS,
            );
        } catch (\Exception $exception) {
            $buyer = null;
        }

        if (null === $buyer) {
            throw new NotFoundException(
                "The buyer with ID '{$userId}' is not found",
                static::USER_NOT_FOUND_ERROR,
                null === $exception ? null : LegacyQueryException::executionFailed($this->companiesRepository->db, $exception, static::STORAGE_WRITE_ERROR)
            );
        }
        if (GroupType::from(GroupType::BUYER) !== GroupType::from($buyer['gr_type'])) {
            throw new AccessDeniedException(
                'Only buyer can be attached to the sample order.',
                static::USER_ACCESS_DENIED_ERROR
            );
        }

        return $buyer;
    }

    /**
     * Returns the order departure location metadata.
     */
    private function getOrderDepartureLocation(array $seller, array $location): ParameterBag
    {
        return new ParameterBag(
            \array_filter([
                'address'     => $seller['address'] ?? null,
                'postalCode'  => $seller['postalCode'] ?? null,
                'fullAddress' => \implode(', ', \array_filter([
                    $location['country'] ?? null,
                    $location['region'] ?? null,
                    $location['city'] ?? null,
                    $seller['postalCode'] ?? null,
                    $seller['address'] ?? null,
                ])),
                'countryId'   => isset($location['countryId']) ? (int) $location['countryId'] : null,
                'country'     => $location['country'] ?? null,
                'stateId'     => isset($location['regionId']) ? (int) $location['regionId'] : null,
                'state'       => $location['region'] ?? null,
                'cityId'      => isset($location['cityId']) ? (int) $location['cityId'] : null,
                'city'        => $location['city'] ?? null,
            ])
        );
    }

    /**
     * Returns the order destination location.
     */
    private function getOrderDestinationLocation(array $buyer, array $location): ParameterBag
    {
        return new ParameterBag(
            \array_filter([
                'address'     => $location['address'] ?? $buyer['address'] ?? null,
                'postalCode'  => $location['postalCode'] ?? $buyer['postalCode'] ?? null,
                'fullAddress' => \implode(', ', \array_filter([
                    $location['nameCountry'] ?? $location['country'] ?? null,
                    $location['nameState'] ?? $location['state'] ?? null,
                    $location['nameCity'] ?? $location['city'] ?? null,
                    $location['postalCode'] ?? $buyer['postalCode'] ?? null,
                    $location['address'] ?? $buyer['address'] ?? null,
                ])),
                'countryId'   => isset($location['idCountry']) ? (int) $location['idCountry'] : null,
                'country'     => $location['nameCountry'] ?? $location['country'] ?? null,
                'stateId'     => isset($location['idState']) ? (int) $location['idState'] : null,
                'state'       => $location['nameState'] ?? $location['state'] ?? null,
                'cityId'      => isset($location['idCity']) ? (int) $location['idCity'] : null,
                'city'        => $location['nameCity'] ?? $location['city'] ?? null,
            ])
        );
    }

    /**
     * Makes product oinstance from item snapshot.
     */
    private function makeProductFromSnapshot(array $originalItem, array $snapshot, int $quantity, Money $itemPrice): array
    {
        return [
            'type'             => 'sample',
            'item_id'          => $snapshot['idItem'],
            'snapshot_id'      => $snapshot['idSnapshot'],
            'name'             => $snapshot['title'] ?? null,
            'unit_price'       => \moneyToDecimal(\priceToUsdMoney($snapshot['price'] ?? 0)),
            'total_price'      => \moneyToDecimal($itemPrice),
            'quantity'         => $quantity,
            'details'          => null,
            'weight'           => $snapshot['itemWeight'] ?? null,
            'length'           => $snapshot['itemLength'] ?? null,
            'width'            => $snapshot['itemWidth'] ?? null,
            'height'           => $snapshot['itemHeight'] ?? null,
            'hs_tariff_number' => $snapshot['hsTariffNumber'] ?? null,
            'country_iso'      => $snapshot['countryAbr'] ?? null,
            'image'            => $snapshot['mainImage'] ?? null,
            'reviews'          => $snapshot['snapshotReviewsCount'] ?? null,
            'rating'           => $snapshot['snapshotRating'] ?? null,
            'unit_type'        => [
                'id'   => $originalItem['unitType'],
                'name' => $originalItem['unitName'],
            ],
        ];
    }

    /**
     * Adds one sample order to the storage.
     *
     * @throws QueryException if failed to save sample order
     */
    private function addSampleOrder(
        ?int $sellerId,
        ?int $buyerId,
        ?string $roomId,
        float $orderWeight,
        Money $finalPrice,
        Collection $products,
        Collection $timeline,
        ?ParameterBag $departureLocation,
        ?ParameterBag $destinationLocation,
        array $users = [],
        ?string $description = null,
        ?string $specification = null
    ): int {
        //region PO locations
        $departureLocation = $departureLocation ?? new ParameterBag();
        $destinationLocation = $destinationLocation ?? new ParameterBag();
        $poDepartureLocation = [
            'full_address' => $departureLocation->get('fullAddress') ?? null,
            'address'      => $departureLocation->get('address') ?? null,
            'country'      => $departureLocation->get('countryId') ?? null,
            'state'        => $departureLocation->get('stateId') ?? null,
            'city'         => $departureLocation->get('cityId') ?? null,
            'zip'          => $departureLocation->get('postalCode') ?? null,
        ];
        $poDestinationLocation = [
            'full_address' => $destinationLocation->get('fullAddress') ?? null,
            'address'      => $destinationLocation->get('address') ?? null,
            'country'      => $destinationLocation->get('countryId') ?? null,
            'state'        => $destinationLocation->get('stateId') ?? null,
            'city'         => $destinationLocation->get('cityId') ?? null,
            'zip'          => $destinationLocation->get('postalCode') ?? null,
        ];

        $isDeliverable = null !== $departureLocation
            && null !== $destinationLocation
            && count($poDepartureLocation) === count(array_filter($poDepartureLocation))
            && count($poDestinationLocation) === count(array_filter($poDestinationLocation));
        //endregion PO locations

        $order = [
            'id_buyer'                => $buyerId,
            'id_seller'               => $sellerId,
            'id_theme'                => null,
            'description'             => $description ?? null,
            'weight'                  => $orderWeight,
            'price'                   => \moneyToDecimal($finalPrice),
            'final_price'             => \moneyToDecimal($finalPrice),
            'ship_from'               => $departureLocation->get('fullAddress') ?? null,
            'ship_from_zip'           => $departureLocation->get('postalCode') ?? null,
            'ship_from_city'          => $departureLocation->get('cityId') ?? null,
            'ship_from_state'         => $departureLocation->get('stateId') ?? null,
            'ship_from_country'       => $departureLocation->get('countryId') ?? null,
            'ship_from_address'       => $departureLocation->get('address') ?? null,
            'ship_to'                 => $destinationLocation->get('fullAddress') ?? null,
            'ship_to_zip'             => $destinationLocation->get('postalCode') ?? null,
            'ship_to_city'            => $destinationLocation->get('cityId') ?? null,
            'ship_to_state'           => $destinationLocation->get('stateId') ?? null,
            'ship_to_country'         => $destinationLocation->get('countryId') ?? null,
            'ship_to_address'         => $destinationLocation->get('address') ?? null,
            'purchase_order_timeline' => $timeline->toArray(),
            'purchased_products'      => $products->toArray(),
            'purchase_order'          => [
                'invoice'         => ['notes' => $specification ?? null],
                'shipping_to'     => $poDestinationLocation,
                'is_edited'       => false,
                'is_confirmed'    => false,
                'is_confirmable'  => false,
                'is_deliverable'  => $isDeliverable,
                'products_weight' => $orderWeight,
                'shipping_from'   => $poDepartureLocation,
            ],
        ];

        try {
            if (!($orderId = $this->sampleOrdersRepository->insertOne($order))) {
                throw LegacyQueryException::executionFailed($this->sampleOrdersRepository->db, null, static::STORAGE_WRITE_ERROR);
            }
        } catch (\Exception $exception) {
            if (!$exception instanceof QueryException) {
                $exception = LegacyQueryException::executionFailed($this->sampleOrdersRepository->db, $exception, static::STORAGE_WRITE_ERROR);
            }

            throw $exception;
        }

        $this->updateSampleOrderSearchInformation((int) $orderId, $users, $finalPrice, $products);
        // After the sample order was created, and we had the matrix room ID, we need to
        // bind the sample order to this room.
        if (null !== $roomId) {
            $this->chatBindings->bindResourceToRoom(
                ResourceOptions::fromRaw(ResourceType::from(ResourceType::SAMPLE_ORDER), (string) $orderId ?: null),
                $roomId,
                $sellerId,
                $buyerId
            );
        }

        return (int) $orderId;
    }

    /**
     * Updates the sample order.
     *
     * @throws QueryException if update in the repository failed
     *
     * @deprecated
     */
    private function updateSampleOrder(int $orderId, array $sampleOrder): void
    {
        try {
            if (!$this->sampleOrdersRepository->updateOne($orderId, $sampleOrder)) {
                throw LegacyQueryException::executionFailed($this->sampleOrdersRepository->db, null, static::STORAGE_UPDATE_ERROR);
            }
        } catch (QueryException $exception) {
            throw $exception; // We rollin'...
        } catch (\Exception $exception) {
            throw LegacyQueryException::executionFailed($this->sampleOrdersRepository->db, $exception, static::STORAGE_UPDATE_ERROR);
        }
    }

    /**
     * Updates sample order search information.
     */
    private function updateSampleOrderSearchInformation(
        int $orderId,
        array $users,
        Money $price,
        Collection $products,
        ?string $destination = null
    ): void {
        $searchParts = [\orderNumber($orderId)];
        foreach ($users as $user) {
            $username = $user['fullname'] ?? null;
            if (null === $username) {
                $username = trim($user['fname'] ?? null . ' ' . $user['lname'] ?? null);
            }

            $searchParts[] = $username;
            $searchParts[] = $user['company'] ?? null;
        }

        $searchParts[] = $destination ?? null;
        $searchParts = \array_merge($searchParts, \array_filter($products->map(function ($product) { return $product['name'] ?? null; })->toArray()));
        $searchParts[] = \moneyToDecimal($price);

        $this->updateSampleOrder($orderId, ['search_tokens' => \implode(', ', \array_filter($searchParts))]);
    }

    /**
     * Send notifications.
     */
    private function sendNotifications(
        int $orderId,
        int $senderId,
        array $recipients,
        ?string $notificationCode = null,
        ?string $messageCodeTitle = null,
        ?string $messageCodeText = null,
        array $replacements = [],
        array $rooms = []
    ): void {
        // If there is no recipients then we don't need to send anything.
        if (empty($recipients)) {
            return;
        }

        // If both message and notification codes are empty, then we we don't need to send anything as well.
        if (null === $notificationCode && null === $messageCodeTitle && null === $messageCodeText) {
            return;
        }

        // Send legacy notification
        if (null !== $notificationCode) {
            // First, let's get the legacy notification model and send it directly
            // TODO: remake it to use notifier
            /** @var \Notify_Model $notificationsModel */
            $notificationsModel = model(\Notify_Model::class);
            $notificationsModel->send_notify([
                'systmess'  => true,
                'mess_code' => $notificationCode,
                'id_users'  => $recipients,
                'replace'   => $replacements,
            ]);
        }

        // Send notification to the matrix
        if (null !== $messageCodeTitle || null !== $messageCodeText) {
            if (empty($rooms)) {
                // Create the type for resource
                $resourceOptions = ResourceOptions::fromRaw(ResourceType::from(ResourceType::SAMPLE_ORDER), (string) $orderId);
                // Define the finder for the room IDs - the generator that will loop over all recipients and get the rooms with them
                $roomIdsFinder = function (array $recipients) use ($senderId, $resourceOptions): iterable {
                    foreach ($recipients as $recipientId) {
                        try {
                            $room = $this->chatBindings->getRoomBindings($resourceOptions, $senderId, $recipientId);
                        } catch (NotFoundException $e) {
                            // Skip if not found - we cannot send the messages to the unexisting room.
                            continue;
                        }

                        yield $room['room']['room_id'];
                    }
                };
                // Filter rooms
                foreach ($roomIdsFinder($recipients) as $roomId) {
                    if (!in_array($roomId, $rooms)) {
                        $rooms[] = $roomId;
                    }
                }
            }

            // Create notification message
            $messageTitle = translate($messageCodeTitle ?? $messageCodeText, $replacements) ?: null;
            $messageText = translate($messageCodeText, $replacements) ?: null;
            // Get the notifier instance
            /** @var NotifierInterface $notifier */
            $notifier = container()->get(NotifierInterface::class);
            foreach ($rooms as $roomId) {
                if (null === $roomId) {
                    continue;
                }

                $notifier->send(new MatrixNotification($roomId, $messageTitle ?? $messageText, $messageText));
            }
        }
    }
}
