<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use ExportPortal\Contracts\Chat\Recource\ResourceType;

/**
 * @deprecated
 */
final class ChatService
{
    private \Matrix_Rooms_Users_Pivot_Model $matrixRoomsUsersPivotModel;
    private \Matrix_Rooms_Orders_Pivot_Model $matrixRoomsOrdersPivotModel;
    private \Matrix_Rooms_Inquiry_Pivot_Model $matrixRoomsInquiryPivotModel;
    private \Matrix_Rooms_Estimate_Pivot_Model $matrixRoomsEstimatePivotModel;
    private \Matrix_Rooms_Offers_Pivot_Model $matrixRoomsOffersPivotModel;
    private \Matrix_Rooms_Po_Pivot_Model $matrixRoomsPoPivotModel;
    private \Matrix_Rooms_B2b_Pivot_Model $matrixRoomsB2bPivotModel;
    private \Matrix_Rooms_B2b_Response_Pivot_Model $matrixRoomsB2bResponsePivotModel;
    private \Matrix_Rooms_Upcoming_Orders_Pivot_Model $matrixRoomsUpcomingOrdersPivotModel;
    private \Matrix_Rooms_Order_Bids_Pivot_Model $matrixRoomsOrderBidsPivotModel;
    private \Matrix_Rooms_Sample_Order_Pivot_Model $matrixRoomsSampleOrderPivotModel;

    private $modules = [
        'b2b'               => 2,
        'b2b_response'      => 3,
        'estimate'          => 5,
        'inquiry'           => 7,
        'order'             => 9,
        'po'                => 11,
        'offer'             => 16,
        'upcoming_orders'   => 32,
        'order_bids'        => 33,
        'sample_order'      => 35,
    ];

    /**
     * List of theme subjects per module.
     *
     * @var array
     */
    private $themeSubjectsPerModule = [
        // 1  => 'Bill [ID]',
        3  => 'B2B Response [ID]',
        // 4  => 'Dispute [ID]',
        7  => 'Inquiry [ID]',
        // 8  => 'Invoice [ID]',
        9  => 'Order [ID]',
        // 10 => 'User [ID]',
        11 => 'Producing Requests [ID]',
        // 12 => 'Groups [ID]',
        // 13 => 'Report [ID]',
        // 14 => 'Company [ID]',
        17 => 'Blog [ID]',
        // 18 => 'Staff [ID]',
        // 19 => 'Branch [ID]',
        // 20 => 'Message [ID]',
        // 21 => 'Question [ID]',
        // 22 => 'Other [ID]',
        // 23 => 'Prototype [ID]',
        // 24 => 'Cancel the order [ID]',
        // 25 => 'Review [ID]',
        // 26 => 'Feedback [ID]',
        // 27 => 'Order document [ID]',
        // 28 => 'Accreditation',
        // 31 => 'Expense reports',
        32 => 'Bidding on order [ID]',
        33 => 'Order bid [ID]',
        35 => 'Sample Order request [ID]',
    ];

    /**
     * Creates instance of the comment list service.
     */
    public function __construct()
    {
        $this->matrixRoomsUsersPivotModel = \model(\Matrix_Rooms_Users_Pivot::class);
        $this->matrixRoomsOrdersPivotModel = \model(\Matrix_Rooms_Orders_Pivot_Model::class);
        $this->matrixRoomsInquiryPivotModel = \model(\Matrix_Rooms_Inquiry_Pivot_Model::class);
        $this->matrixRoomsEstimatePivotModel = \model(\Matrix_Rooms_Estimate_Pivot_Model::class);
        $this->matrixRoomsOffersPivotModel = \model(\Matrix_Rooms_Offers_Pivot_Model::class);
        $this->matrixRoomsPoPivotModel = \model(\Matrix_Rooms_Po_Pivot_Model::class);
        $this->matrixRoomsB2bPivotModel = \model(\Matrix_Rooms_B2b_Pivot_Model::class);
        $this->matrixRoomsB2bResponsePivotModel = \model(\Matrix_Rooms_B2b_Response_Pivot_Model::class);
        $this->matrixRoomsUpcomingOrdersPivotModel = \model(\Matrix_Rooms_Upcoming_Orders_Pivot_Model::class);
        $this->matrixRoomsOrderBidsPivotModel = \model(\Matrix_Rooms_Order_Bids_Pivot_Model::class);
        $this->matrixRoomsSampleOrderPivotModel = \model(\Matrix_Rooms_Sample_Order_Pivot_Model::class);
    }

    /**
     * @deprecated
     */
    public function findRoom(int $recipientId, int $userId): array
    {
        $item = $this->matrixRoomsUsersPivotModel->getRoom($userId, $recipientId);

        return $item ?? [];
    }

    /**
     * Makes subject for provided type.
     *
     * @param ResourceType $resourceType the resource type
     * @param int          $senderId     the sender ID
     * @param null|int     $recipientId  the recipient ID
     * @param null|int     $resourceId   the resource ID
     */
    public function makeSubjectForType(ResourceType $resourceType, int $senderId, ?int $recipientId, ?int $resourceId): string
    {
        try {
            $subjectParts = $this->createSubjectPartsForType($resourceType, $recipientId, $senderId, $resourceId);
        } catch (NotFoundException $exception) {
            if (null === $fallbackSubject = $this->getFallbackSubjectForType($resourceType)) {
                throw $exception;
            }

            $subjectParts = [
                str_replace('[ID]', orderNumber($resourceId), $fallbackSubject),
            ];
        }

        return implode(': ', $subjectParts);
    }

    /**
     * Get room for provided type.
     *
     * @param ResourceType $resourceType the resource type
     * @param int          $senderId     the sender ID
     * @param null|int     $recipientId  the recipient ID
     * @param null|int     $resourceId   the resource ID
     *
     * @throws NotFoundException when room for provided type is not found is not found
     */
    public function getRoomForType(ResourceType $resourceType, int $senderId, ?int $recipientId, ?int $resourceId): array
    {
        $room = null;

        switch ($resourceType) {
            case ResourceType::from(ResourceType::B2B):
                $room = $this->matrixRoomsB2bPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::B2B_RESPONSE):
                $room = $this->matrixRoomsB2bResponsePivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::ESTIMATE):
                $room = $this->matrixRoomsEstimatePivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::INQUIRY):
                $room = $this->matrixRoomsInquiryPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::ORDER):
                $room = $this->matrixRoomsOrdersPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::OFFER):
                $room = $this->matrixRoomsOffersPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::PO):
                $room = $this->matrixRoomsPoPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::UPCOMING_ORDER):
                $room = $this->matrixRoomsUpcomingOrdersPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::ORDER_BID):
                $room = $this->matrixRoomsOrderBidsPivotModel->getRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::SAMPLE_ORDER):
                $room = $this->matrixRoomsSampleOrderPivotModel->findRoom($resourceId, $senderId, $recipientId);

                break;

            case ResourceType::from(ResourceType::USER):
                $room = $this->matrixRoomsUsersPivotModel->getRoom($senderId, $recipientId);

                break;
        }
        if (null === $room) {
            throw new NotFoundException('The room for provided typ is not found.');
        }

        return $room;
    }

    /**
     * @deprecated
     */
    public function findThemeFromModule(int $recipientId, int $userId, ?int $moduleId, ?int $itemId): array
    {
        try {
            $subjectParts = $this->createSubjectForModule($recipientId, $userId, $moduleId, $itemId);
        } catch (NotFoundException $exception) {
            if (!isset($this->themeSubjectsPerModule[$moduleId])) {
                throw $exception;
            }
        }

        $room = $this->getRooms($recipientId, $userId, $moduleId, $itemId);

        if (empty($subjectParts) && isset($this->themeSubjectsPerModule[$moduleId])) {
            $subjectParts = [
                str_replace('[ID]', orderNumber($itemId), $this->themeSubjectsPerModule[$moduleId]),
            ];
        }

        $subject = implode(': ', $subjectParts);

        return [$subject, $room];
    }

    /**
     * @deprecated
     */
    public function insertRoomByModule(int $roomId, int $recipientId, int $userId, ?int $moduleId, ?int $itemId): int
    {
        $id = 0;
        $date = new \DateTimeImmutable();

        if (!empty($moduleId) && $moduleId > 0) {
            switch ($moduleId) {
                case $this->modules['b2b']:
                    if ($roomId > 0) {
                        $this->matrixRoomsB2bPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsB2bPivotModel->add(
                            [
                                [
                                    'id_b2b'            => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_b2b'            => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                    break;

                case $this->modules['b2b_response']:
                    if ($roomId > 0) {
                        $this->matrixRoomsB2bResponsePivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsB2bResponsePivotModel->add(
                            [
                                [
                                    'id_b2b_response'   => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_b2b_response'   => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                    break;

                case $this->modules['estimate']:
                    if ($roomId > 0) {
                        $this->matrixRoomsEstimatePivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsEstimatePivotModel->add(
                            [
                                [
                                    'id_estimate'       => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_estimate'        => $itemId,
                                    'id_sender'          => $recipientId,
                                    'id_recipient'       => $userId,
                                    'id_room'            => $roomId,
                                    'created_at_date'    => $date,
                                ],
                            ]
                        );
                    }

                    break;

                case $this->modules['inquiry']:
                    if ($roomId > 0) {
                        $this->matrixRoomsInquiryPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsInquiryPivotModel->add(
                            [
                                [
                                    'id_inquiry'        => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_inquiry'        => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['order']:
                    if ($roomId > 0) {
                        $this->matrixRoomsOrdersPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsOrdersPivotModel->add(
                            [
                                [
                                    'id_order'          => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_order'          => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['po']:
                    if ($roomId > 0) {
                        $this->matrixRoomsPoPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsPoPivotModel->add(
                            [
                                [
                                    'id_po'             => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_po'             => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['offer']:
                    if ($roomId > 0) {
                        $this->matrixRoomsOffersPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsOffersPivotModel->add(
                            [
                                [
                                    'id_offer'          => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_offer'          => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['upcoming_orders']:
                    if ($roomId > 0) {
                        $this->matrixRoomsUpcomingOrdersPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsUpcomingOrdersPivotModel->add(
                            [
                                [
                                    'id_upcoming_order' => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_upcoming_order' => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['order_bids']:
                    if ($roomId > 0) {
                        $this->matrixRoomsOrderBidsPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsOrderBidsPivotModel->add(
                            [
                                [
                                    'id_order_bid'      => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_order_bid'      => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;

                case $this->modules['sample_order']:
                    if ($roomId > 0) {
                        $this->matrixRoomsSampleOrderPivotModel->update([$recipientId, $userId], [$userId, $recipientId], $itemId, ['id_room' => $roomId]);
                    } else {
                        $id = $this->matrixRoomsSampleOrderPivotModel->add(
                            [
                                [
                                    'id_sample_order'   => $itemId,
                                    'id_sender'         => $userId,
                                    'id_recipient'      => $recipientId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                                [
                                    'id_sample_order'   => $itemId,
                                    'id_sender'         => $recipientId,
                                    'id_recipient'      => $userId,
                                    'id_room'           => $roomId,
                                    'created_at_date'   => $date,
                                ],
                            ]
                        );
                    }

                break;
            }
        } else {
            $id = $this->matrixRoomsUsersPivotModel->add(
                [
                    [
                        'id_sender'         => $userId,
                        'id_recipient'      => $recipientId,
                        'id_room'           => $roomId,
                        'created_at_date'   => $date,
                    ],
                    [
                        'id_sender'         => $recipientId,
                        'id_recipient'      => $userId,
                        'id_room'           => $roomId,
                        'created_at_date'   => $date,
                    ],
                ]
            );
        }

        return $id;
    }

    /**
     * @deprecated
     */
    private function getRooms(int $userId, int $recipientId, ?int $moduleId, ?int $itemId): array
    {
        if (!empty($moduleId) && empty($itemId)) {
            throw new NotFoundException('The room is not found.');
        }

        if (!empty($moduleId) && $moduleId > 0) {
            switch ($moduleId) {
                case $this->modules['b2b']:
                    $item = $this->matrixRoomsB2bPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['b2b_response']:
                    $item = $this->matrixRoomsB2bResponsePivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['estimate']:
                    $item = $this->matrixRoomsEstimatePivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['inquiry']:
                    $item = $this->matrixRoomsInquiryPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['order']:
                    $item = $this->matrixRoomsOrdersPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['po']:
                    $item = $this->matrixRoomsPoPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['offer']:
                    $item = $this->matrixRoomsOffersPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['upcoming_orders']:
                    $item = $this->matrixRoomsUpcomingOrdersPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['order_bids']:
                    $item = $this->matrixRoomsOrderBidsPivotModel->getRoom($itemId, $userId, $recipientId);

                break;

                case $this->modules['sample_order']:
                    $item = $this->matrixRoomsSampleOrderPivotModel->getRoom($itemId, $userId, $recipientId);

                break;
            }
        } else {
            $item = $this->matrixRoomsUsersPivotModel->getRoom($userId, $recipientId);
        }

        return $item ?? [];
    }

    private function getUsersTypeParams(int $recipientId, ?int $userId = 0, ?array $types = ['Seller' => 'id_seller', 'Buyer' => 'id_buyer', 'Shipper' => 'id_shipper']): array
    {
        $params = [];
        /** @var \User_Model $userRepository */
        $userRepository = \model(\User_Model::class);
        $usersList[] = $recipientId;

        if (0 !== $userId) {
            $usersList[] = $userId;
        }

        $users = $userRepository->getUsers(['users_list' => implode(',', $usersList)]);
        $users = arrayByKey($users, 'idu');

        $recipientType = $users[$recipientId]['gr_type'];
        $params[$types[$recipientType]] = $recipientId;

        if (0 !== $userId) {
            $userType = $users[$userId]['gr_type'];
            $params[$types[$userType]] = $userId;
        }

        return $params;
    }

    private function getFallbackSubjectForType(ResourceType $resourceType): ?string
    {
        switch ($resourceType) {
            case ResourceType::from(ResourceType::PO): return 'Producing Requests [ID]';

            case ResourceType::from(ResourceType::B2B): return 'B2B Request [ID]';

            case ResourceType::from(ResourceType::USER): return null;

            case ResourceType::from(ResourceType::ORDER): return 'Order [ID]';

            case ResourceType::from(ResourceType::OFFER): return 'Offer [ID]';

            case ResourceType::from(ResourceType::INQUIRY): return 'Inquiry [ID]';

            case ResourceType::from(ResourceType::ESTIMATE): return 'Estimate [ID]';

            case ResourceType::from(ResourceType::ORDER_BID): return 'Order bid [ID]';

            case ResourceType::from(ResourceType::B2B_RESPONSE): return 'B2B Response [ID]';

            case ResourceType::from(ResourceType::SAMPLE_ORDER): return 'Sample Order request [ID]';

            case ResourceType::from(ResourceType::UPCOMING_ORDER): return 'Bidding on order [ID]';

            default:
                return null;
        }
    }

    /**
     * Creates the subject parts using provided user ID, module ID and item ID.
     *
     * @return string[][]
     *
     * @deprecated
     */
    private function createSubjectForModule(int $recipientId, int $userId, ?int $moduleId, ?int $itemId): array
    {
        switch ($moduleId) {
            case $this->modules['b2b']:
                return $this->createSubjectPartsForB2bType($recipientId, $itemId);

            case $this->modules['b2b_response']:
                return $this->createSubjectPartsForB2bResponseType($recipientId, $itemId);

            case $this->modules['estimate']:
                return $this->createSubjectPartsForEstimateType($userId, $recipientId, $itemId);

            case $this->modules['inquiry']:
                return $this->createSubjectPartsForInquiryType($userId, $recipientId, $itemId);

            case $this->modules['order']:
                return $this->createSubjectPartsForOrderType($userId, $recipientId, $itemId);

            case $this->modules['offer']:
                return $this->createSubjectPartsForOfferType($userId, $recipientId, $itemId);

            case $this->modules['po']:
                return $this->createSubjectPartsForPoType($userId, $recipientId, $itemId);

            case $this->modules['upcoming_orders']:
                return $this->createSubjectPartsForUpcomingOrderType($recipientId, $itemId);

            case $this->modules['order_bids']:
                return $this->createSubjectPartsForOrderBidType($recipientId, $itemId);

            case $this->modules['sample_order']:
                return $this->createSubjectPartsForSampleOrderType($userId, $recipientId, $itemId);
            // case 6: // event
            //     if (empty($eventDetails = model('events')->getEvent($itemId))) {
            //         throw new NotFoundException('The event is not found');
            //     }
            //     $subject = ['Event ' . orderNumber($itemId), cut_str_with_dots($eventDetails['title_event'], 20)];

            //     break;
            // case 15: // item
            //     if (empty($itemsDetails = model('items')->get_item($itemId, 'title'))) {
            //         throw new NotFoundException('The item is not found');
            //     }
            //     $subject = ['Item ' . orderNumber($itemId), cut_str_with_dots($itemsDetails['title'], 20)];

            //     break;
            // case 27: // order document
            //     if (
            //         empty($orderDocument = model('orders_docs')->get_order_document($itemId))
            //         || (!is_privileged('user', $orderDocument['id_assigner']) && 0 == $orderDocument['doc_active'])
            //     ) {
            //         throw new NotFoundException('The order document is not found.');
            //     }

            //     if (empty($orderDetails = model('orders')->get_order($orderId = (int) $orderDocument['id_order']))) {
            //         throw new NotFoundException('The order is not found.');
            //     }

            //     $orderUsers = array_filter(
            //         [
            //             $orderDetails['id_buyer'] ?? null,
            //             $orderDetails['id_seller'] ?? null,
            //             $orderDetails['ep_manager'] ?? null,
            //             'ep_shipper' === $orderDetails['shipper_type'] ? ($orderDetails['id_shipper'] ?? null) : null,
            //         ]
            //     );
            //     if (!in_array($userId, $orderUsers)) {
            //         throw new OwnershipException('You are not assigned to this order.');
            //     }

            //     $subject = [
            //         'About the document ' . orderNumber($itemId), cut_str_with_dots($orderDocument['doc_title'], 20), ' for order ' . orderNumber($orderId),
            //     ];

            //     break;
            // case 28: // Accreditation
            //     if (empty($verificationDocument = model(\Verification_Document_Types_Model::class)->findOne($itemId))) {
            //         throw new NotFoundException('The verification document type is not found.');
            //     }
            //     $subject = ['About accreditation document', cut_str_with_dots(
            //         accreditation_i18n($verificationDocument['document_i18n'], 'title', null, $verificationDocument['document_title']),
            //         20
            //     )];

            //     break;
            // case 31: // Expense reports
            //     if (empty($expenseReport = model('cr_expense_reports')->get_report($itemId))) {
            //         throw new NotFoundException('The expense report is not found.');
            //     }
            //     $subject = ['About expense report', cut_str_with_dots($expenseReport['ereport_title'], 20)];

            //     break;
        }

        return [];
    }

    /**
     * Creates the subject parts using provided user ID, resource type and item ID.
     *
     * @return string[][]
     */
    private function createSubjectPartsForType(ResourceType $resourceType, int $recipientId, int $userId, ?int $itemId): array
    {
        switch ($resourceType) {
            case ResourceType::from(ResourceType::B2B):
                return $this->createSubjectPartsForB2bType($recipientId, $itemId);

            case ResourceType::from(ResourceType::B2B_RESPONSE):
                return $this->createSubjectPartsForB2bResponseType($recipientId, $itemId);

            case ResourceType::from(ResourceType::ESTIMATE):
                return $this->createSubjectPartsForEstimateType($userId, $recipientId, $itemId);

            case ResourceType::from(ResourceType::INQUIRY):
                return $this->createSubjectPartsForInquiryType($userId, $recipientId, $itemId);

            case ResourceType::from(ResourceType::ORDER):
                return $this->createSubjectPartsForOrderType($userId, $recipientId, $itemId);

            case ResourceType::from(ResourceType::OFFER):
                return $this->createSubjectPartsForOfferType($userId, $recipientId, $itemId);

            case ResourceType::from(ResourceType::PO):
                return $this->createSubjectPartsForPoType($userId, $recipientId, $itemId);

            case ResourceType::from(ResourceType::UPCOMING_ORDER):
                return $this->createSubjectPartsForUpcomingOrderType($recipientId, $itemId);

            case ResourceType::from(ResourceType::ORDER_BID):
                return $this->createSubjectPartsForOrderBidType($recipientId, $itemId);

            case ResourceType::from(ResourceType::SAMPLE_ORDER):
                return $this->createSubjectPartsForSampleOrderType($userId, $recipientId, $itemId);
        }

        return [];
    }

    /**
     * Creates subject parts for B2B resource type.
     */
    private function createSubjectPartsForB2bType(int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for B2B request is required.');
        }

        /** @var \B2b_Model $b2bRepository */
        $b2bRepository = \model(\B2b_Model::class);
        if (empty($b2bDetails = $b2bRepository->get_b2b_request($itemId, ['id_user' => $recipientId]))) {
            throw new NotFoundException('The B2B request is not found.');
        }

        return ['B2B Request ' . orderNumber($itemId), cut_str_with_dots($b2bDetails['b2b_title'], 20)];
    }

    /**
     * Creates subject parts for B2B response type.
     */
    private function createSubjectPartsForB2bResponseType(int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for B2B response is required.');
        }

        /** @var \B2b_Model $b2bRepository */
        $b2bRepository = \model(\B2b_Model::class);
        if (empty($b2bRepository->get_response($itemId, ['id_user' => $recipientId]))) {
            throw new NotFoundException('The B2B Response is not found.');
        }

        return [str_replace('[ID]', orderNumber($itemId), $this->getFallbackSubjectForType(ResourceType::from(ResourceType::B2B_RESPONSE)))];
    }

    /**
     * Creates subject parts for order response type.
     */
    private function createSubjectPartsForOrderType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for order is required.');
        }

        /** @var \Orders_Model $orderRepository */
        $orderRepository = \model(\Orders_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId);
        if (empty($orderRepository->get_order($itemId, [], $params))) {
            throw new NotFoundException('The Order is not found.');
        }

        return [str_replace('[ID]', orderNumber($itemId), $this->getFallbackSubjectForType(ResourceType::from(ResourceType::ORDER)))];
    }

    /**
     * Creates subject parts for upcoming order response type.
     */
    private function createSubjectPartsForUpcomingOrderType(int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for upcoming order is required.');
        }

        /** @var \Orders_Model $orderRepository */
        $orderRepository = \model(\Orders_Model::class);
        $params = $this->getUsersTypeParams($recipientId);
        if (empty($orderRepository->get_order($itemId, [], $params))) {
            throw new NotFoundException('The Upcoming order is not found.');
        }

        return [str_replace(['[ID]'], [orderNumber($itemId)], $this->getFallbackSubjectForType(ResourceType::from(ResourceType::UPCOMING_ORDER)))];
    }

    /**
     * Creates subject parts for order bids response type.
     */
    private function createSubjectPartsForOrderBidType(int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for order bid is required.');
        }

        /** @var \Orders_Quotes_Model $orderdQuotesRepository */
        $orderdQuotesRepository = \model(\Orders_Quotes_Model::class);
        $params = $this->getUsersTypeParams($recipientId);
        if (empty($bid = $orderdQuotesRepository->get_bid($itemId, ['joins' => ['orders'], 'conditions' => $params]))) {
            throw new NotFoundException('The order bid is not found.');
        }

        return [
            str_replace(
                ['[BID]', '[ORDER]'],
                [orderNumber($bid['id_quote']), orderNumber($bid['id_order'])],
                'Bid [BID] on order [ORDER]'
            ),
        ];
    }

    /**
     * Creates subject parts for sample order response type.
     */
    private function createSubjectPartsForSampleOrderType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for sample order is required.');
        }

        /** @var \Sample_Orders_Model $sampleOrderRepository */
        $sampleOrderRepository = \model(\Sample_Orders_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId, ['Seller' => 'seller', 'Buyer' => 'buyer', 'Shipper' => 'shipper']);
        if (empty($sample_order = $sampleOrderRepository->get_sample($itemId, ['conditions' => $params]))) {
            throw new NotFoundException('The sample order is not found.');
        }

        return [
            str_replace(
                ['[ORDER]'],
                [orderNumber($sample_order['id'])],
                'Sample Order request [ORDER]'
            ),
        ];
    }

    /**
     * Creates subject parts for estimate response type.
     */
    private function createSubjectPartsForEstimateType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for estimate is required.');
        }

        /** @var \Estimate_Model $estimateRepository */
        $estimateRepository = \model(\Estimate_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId);
        if (empty($estimateDetails = $estimateRepository->get_request_estimate($itemId, $params))) {
            throw new NotFoundException('The Estimate is not found.');
        }

        return ['Estimate ' . orderNumber($itemId) . ', for item', cut_str_with_dots($estimateDetails['title'], 20)];
    }

    /**
     * Creates subject parts for inquiry response type.
     */
    private function createSubjectPartsForInquiryType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for inquiry is required.');
        }

        /** @var \Inquiry_Model $inquiryRepository */
        $inquiryRepository = \model(\Inquiry_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId, ['Seller' => 'seller', 'Buyer' => 'buyer']);
        if (empty($inquiryDetails = $inquiryRepository->get_inquiry($itemId, $params))) {
            throw new NotFoundException('The Inquiry is not found.');
        }

        return ['Inquiry ' . orderNumber($itemId) . ', for item', cut_str_with_dots($inquiryDetails['title'], 20)];
    }

    /**
     * Creates subject parts for offer response type.
     */
    private function createSubjectPartsForOfferType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for offer is required.');
        }

        /** @var \Offers_Model $offersRepository */
        $offersRepository = \model(\Offers_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId);
        if (empty($offerDetails = $offersRepository->get_offer($itemId, $params))) {
            throw new NotFoundException('The Offer is not found');
        }

        return ['Offer ' . orderNumber($itemId) . ', for item', cut_str_with_dots($offerDetails['title'], 20)];
    }

    /**
     * Creates subject parts for PO response type.
     */
    private function createSubjectPartsForPoType(int $senderId, int $recipientId, ?int $itemId): array
    {
        if (null === $itemId) {
            throw new NotFoundException('The ID value for offer is required.');
        }

        /** @var \Po_Model $poRepository */
        $poRepository = \model(\Po_Model::class);
        $params = $this->getUsersTypeParams($recipientId, $senderId, ['Seller' => 'seller', 'Buyer' => 'buyer']);
        if (empty($poDetails = $poRepository->get_po_one($itemId, $params))) {
            throw new NotFoundException('The PO is not found.');
        }

        return ['Producing Requests ' . orderNumber($itemId) . ', for item', cut_str_with_dots($poDetails['title'], 20)];
    }
}
