<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\SampleOrders\PurchaseOrderConfirmationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Items_Model;
use Money\Money;
use Sample_Orders_Model;
use Symfony\Component\HttpFoundation\Request;
use User_Bills_Model;
use User_Model;

final class SampleOrdersPopupService implements SampleServiceInterface
{
    use SampleOrdersEntitiesAwareTrait;

    /**
     * The items repository.
     *
     * @var User_Model
     */
    private $usersRepository;

    /**
     * The items repository.
     *
     * @var Items_Model
     */
    private $itemsRepository;

    /**
     * The amount of products per page.
     *
     * @var null|int
     */
    private $productsPerPage;

    /**
     * Creates instance of service.
     *
     * @param Sample_Orders_Model $sampleOrders
     * @param User_Model          $users
     * @param Items_Model         $items
     */
    public function __construct(
        int $productsPerPage = null,
        ?Sample_Orders_Model $sampleOrders = null,
        ?User_Model $users = null,
        ?Items_Model $items = null
    ) {
        $this->usersRepository = $users ?? model(User_Model::class);
        $this->itemsRepository = $items ?? model(Items_Model::class);
        $this->sampleOrdersRepository = $sampleOrders ?? model(Sample_Orders_Model::class);
        $this->productsPerPage = $productsPerPage;
    }

    /**
     * Returns information for create sample order popup.
     */
    public function getCreateOrderPopupInformation(Request $request, int $userId, int $itemId): array
    {
        //region Security
        $this->ensureUserExists($userId);
        $this->ensureItemExists($itemId);
        //endregion Security

        return array(
            'item'      => $this->itemsRepository->get_item($itemId),
            'photos'    => array_filter(array($this->itemsRepository->get_item_main_photo($itemId) ?? null)),
            'is_dialog' => (bool) $request->query->get('dialog', 0),
        );
    }

    /**
     * Returns information for sample request popup.
     */
    public function getRequestPopupInformation(Request $request, int $userId, int $itemId): array
    {
        return with(
            $this->getCreateOrderPopupInformation($request, $userId, $itemId),
            function (array $data) use ($userId) {
                //region Adress
                $user = $this->usersRepository->getSimpleUser($userId);
                $location = $this->usersRepository->get_user_location($userId) ?? array();
                $addressParts = array_filter(array(
                    $user['address'] ?? null,
                    $location['name_country'] ?? null,
                    $location['name_state'] ?? null,
                    $location['name_city'] ?? null,
                    $user['zip'] ?? null,
                ));
                //endregion Adress

                return array_merge($data, array(
                    'address'=> !empty($addressParts) ? implode(', ', $addressParts) : null,
                ));
            }
        );
    }

    /**
     * Returns information for delivery address popup.
     *
     * @throws PurchaseOrderConfirmationException if PO is already confirmed
     */
    public function getDeliveryAddressPopupInformation(int $orderId, int $buyerId, ?array $allowedStatuses = null): array
    {
        $sampleOrder = $this->sampleOrdersRepository->findOneBy(array(
            'conditions'    => array('sample' => $orderId),
            'with'          => array('status', 'buyer', 'destination_country', 'destination_region', 'destination_city'),
        ));

        if (empty($sampleOrder)) {
            throw new NotFoundException('The sample order number is expected', SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        if ($sampleOrder['id_buyer'] != $buyerId) {
            throw new OwnershipException('The order doesn\'t belong to you.', SampleServiceInterface::ORDER_OWNERSHIP_ERROR);
        }

        // Check access for statuses
        if (null !== $allowedStatuses && !in_array($sampleOrder['status']['alias'] ?? null, $allowedStatuses)) {
            throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        //region Order parts
        try {
            $purchaseOrder = $sampleOrder['purchase_order'] ?? '';
            if (!is_array($purchaseOrder)) {
                $purchaseOrder = json_decode($purchaseOrder, true, JSON_THROW_ON_ERROR) ?? [];
            }
        } catch (Exception $exception) {
            throw new AccessDeniedException(
                'Access to sample order denied - sample order is malformed',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR,
                $exception
            );
        }
        //endregion Order parts

        // Check if PO is not confirmed
        if ($purchaseOrder['is_confirmed'] ?? false) {
            throw new PurchaseOrderConfirmationException(
                'The delivery address cannot be updated for if PO is already confirmed.',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR
            );
        }

        $buyer = $sampleOrder['buyer'];
        $buyerLocation = $this->usersRepository->get_user_location($buyerId) ?? array();
        $addressParts = array_filter(array(
            $buyer['address'] ?? null,
            $buyerLocation['name_country'] ?? null,
            $buyerLocation['name_state'] ?? null,
            $buyerLocation['name_city'] ?? null,
            $buyer['zip'] ?? null,
        ));

        if (!empty($sampleOrder['ship_to_country'])) {
            $buyerAddressPartsRaw = array(
                $buyer['country'],
                $buyer['state'],
                $buyer['city'],
                $buyer['zip'],
                $buyer['address']
            );

            $orderAddressPartsRaw = array(
                $sampleOrder['ship_to_country'],
                $sampleOrder['ship_to_state'],
                $sampleOrder['ship_to_city'],
                $sampleOrder['ship_to_zip'],
                $sampleOrder['ship_to_address'],
            );

            if (count(array_uintersect($buyerAddressPartsRaw, $orderAddressPartsRaw, "strcasecmp")) != 5) {
                $otherAddressParts = array_filter(array(
                    $sampleOrder['ship_to_address'] ?? null,
                    $sampleOrder['destination_country']['country'] ?? null,
                    $sampleOrder['destination_region']['state'] ?? null,
                    $sampleOrder['destination_city']['city'] ?? null,
                    $sampleOrder['ship_to_zip'] ?? null,
                ));

                $otherAddressData = array(
                    'country' => array(
                        'value' => $sampleOrder['ship_to_country'],
                        'name'  => $sampleOrder['destination_country']['country'],
                    ),
                    'state' => array(
                        'value' => $sampleOrder['ship_to_state'],
                        'name'  => $sampleOrder['destination_region']['state'],
                    ),
                    'city' => array(
                        'value' => $sampleOrder['ship_to_city'],
                        'name'  => $sampleOrder['destination_city']['city'],
                    ),
                    'postal_code' => array(
                        'value' => $sampleOrder['ship_to_zip'],
                        'name'  => $sampleOrder['ship_to_zip'],
                    ),
                    'address' => array(
                        'value' => $sampleOrder['ship_to_address'],
                        'name'  => $sampleOrder['ship_to_address'],
                    ),
                );
            }
        }
        return array(
            'address'                   => !empty($addressParts) ? implode(', ', $addressParts) : null,
            'id_order'                  => $orderId,
            'order'                     => $sampleOrder,
            'purchase_order'            => $purchaseOrder,
            'other_location'            => $otherAddressData ?? null,
            'other_address_input_value' => !empty($otherAddressParts) ? implode(', ', $otherAddressParts) : null,
        );
    }

    /**
     * Return information for order timeline popup.
     */
    public function getTimelinePopupInformation(int $orderId, int $userId): array
    {
        $sample = $this->sampleOrdersRepository->find($orderId);
        if (empty($sample)) {
            throw new NotFoundException("The sample order with ID '{$orderId}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        if (!in_array($userId, array($sample['id_seller'], $sample['id_buyer']))) {
            throw new OwnershipException('The order doesn\'t belong to you.', SampleServiceInterface::ORDER_OWNERSHIP_ERROR);
        }

        try {
            $data['sample_order_timeline'] = $sample['purchase_order_timeline'] ?? '';
            if (!is_array($data['sample_order_timeline'])) {
                $data['sample_order_timeline'] = json_decode($data['sample_order_timeline'], true, JSON_THROW_ON_ERROR) ?? [];
            }
        } catch (Exception $exception) {
            $data['sample_order_timeline'] = array();
        }

        return $data;
    }

    /**
     * Return tracking information popup.
     *
     * @throws NotFoundException  if sample order is not found
     * @throws OwnershipException if sample order doesn't belong to the user
     */
    public function getTrackingInfoPopupInformation(int $orderId, int $userId): array
    {
        $sample = $this->sampleOrdersRepository->findOneBy(array(
            'columns'       => 'id, tracking_info, delivery_date, id_seller, id_shipper',
            'conditions'    => array(
                'sample' => $orderId,
            ),
            'with'          => array(
                'shipper',
            ),
        ));

        if (empty($sample)) {
            throw new NotFoundException("The sample order with ID '{$orderId}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        if ($userId != $sample['id_seller']) {
            throw new OwnershipException('The order doesn\'t belong to you.', SampleServiceInterface::ORDER_OWNERSHIP_ERROR);
        }

        return $sample;
    }

    /**
     * Returns information for PO (purchase order) popup.
     *
     * @throws NotFoundException     if sample order is not found
     * @throws OwnershipException    if sample order doesn't belong to the user
     * @throws AccessDeniedException if sample order is malformed
     */
    public function getPurchaseOrderInformation(int $userId, ?int $sampleOrderId): array
    {
        //region Security
        if (
            null === $sampleOrderId
            || null === ($sampleOrder = $this->sampleOrdersRepository->findOneBy(array(
                'with'       => array('shipper', 'status'),
                'conditions' => array('order' => $sampleOrderId),
            )))
        ) {
            throw new NotFoundException("The sample order with ID '{$sampleOrderId}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        $buyerId = null !== $sampleOrder['id_buyer'] ? (int) $sampleOrder['id_buyer'] : null;
        $sellerId = null !== $sampleOrder['id_seller'] ? (int) $sampleOrder['id_seller'] : null;
        if ($userId !== $buyerId && $userId !== $sellerId) {
            throw new OwnershipException(
                "The sample with ID '{$sampleOrderId}' doesn't belong to the user '{$userId}'.",
                SampleServiceInterface::ORDER_OWNERSHIP_ERROR
            );
        }
        //endregion Security

        //region Order parts
        try {
            $decode = fn($value) => null === $value || \is_array($value) ? $value : json_decode($value, true, JSON_THROW_ON_ERROR);
            $purchaseOrder = $decode($sampleOrder['purchase_order'] ?? null) ?? [];
            $purchasedProducts = $decode($sampleOrder['purchased_products'] ?? null) ?? [];
            $purchaseOrderTimeline = $decode($sampleOrder['purchase_order_timeline'] ?? null) ?? [];
        } catch (Exception $exception) {
            throw new AccessDeniedException(
                'Access to sample order denied - sample order is malformed',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR,
                $exception
            );
        }
        //endregion Order parts

        return array(
            'po'             => $purchaseOrder ?? array(),
            'order'          => $sampleOrder,
            'status'         => $sampleOrder['status'] ?? null,
            'shipper'        => $sampleOrder['shipper'] ?? null,
            'products'       => $purchasedProducts ?? array(),
            'timeline'       => $purchaseOrderTimeline ?? array(),
            'is_edited'      => $purchaseOrder['is_edited'] ?? false,
            'is_confirmed'   => $purchaseOrder['is_confirmed'] ?? false,
            'is_confirmable' => $purchaseOrder['is_confirmable'] ?? false,
            'is_deliverable' => $purchaseOrder['is_deliverable'] ?? false,
        );
    }

    /**
     * Undocumented function.
     *
     * @param string[] $allowedStatuses
     *
     * @throws AccessDeniedException              if user is not seller
     * @throws PurchaseOrderConfirmationException if PO is already confirmed
     */
    public function getEditPurchaseOrderInformation(int $userId, ?int $sampleOrderId, ?array $allowedStatuses = null): array
    {
        $details = $this->getPurchaseOrderInformation($userId, $sampleOrderId);

        // Check access for statuses
        if (null !== $allowedStatuses && !in_array($details['status']['alias'] ?? null, $allowedStatuses)) {
            throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Deny access for anyone but seller
        if ($userId !== (int) $details['order']['id_seller']) {
            throw new AccessDeniedException('Only seller is allowed purchase order (PO).', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }

        // Check if confirmed
        if ($details['is_confirmed'] ?? false) {
            throw new PurchaseOrderConfirmationException(
                'The PO cannot be updated for if it already confirmed.',
                SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR
            );
        }

        return $details;
    }

    /**
     * Returns information for bills popup.
     *
     * @throws AccessDeniedException if sample order has invalid status
     */
    public function getBillsPopupInfomration(
        SampleOrdersService $sampleOrders,
        User_Bills_Model $bills,
        int $userId,
        ?int $sampleOrderId,
        string $billingStatus
    ): array {
        //region Sample
        $sampleOrder = \arrayCamelizeAssocKeys($sampleOrders->getSampleOrderInformation($sampleOrderId, $userId, false, false, false, true));
        $orderStatus = $sampleOrder['status'] ?? null;
        if (($orderStatus['alias'] ?? null) !== $billingStatus) {
            throw new AccessDeniedException('The sample order has invalid status', SampleServiceInterface::ORDER_ACCESS_DENIED_ERROR);
        }
        //endregion Sample

        //region Bills
        $billStatuses = $bills->get_bills_statuses();
        /** @var Collection $orderBills */
        $orderBills = $sampleOrder['bills'] ?? new ArrayCollection();
        $confirmedAmount = Money::USD(0);
        $refundAmount = Money::USD(0);
        $paidAmount = Money::USD(0);
        if (!empty($orderBills)) {
            $orderBills = $orderBills->map(function (array $bill) use ($paidAmount, $confirmedAmount, $refundAmount) {
                $bill['amount'] = \priceToUsdMoney($bill['amount']);
                $bill['balance'] = \priceToUsdMoney($bill['balance']);
                $bill['total_balance'] = \priceToUsdMoney($bill['total_balance']);
                $bill['bill_balance'] = $billBalance = $bill['balance']->subtract($bill['amount']);
                $bill['pay_detail'] = !empty($bill['pay_detail']) ? unserialize($bill['pay_detail']) : null;
                $bill['note'] = !empty($bill['note']) ? json_decode("[{$bill['note']}]", true) ?? array() : array();

                $paidAmount = $paidAmount->add($bill['amount']);
                if ('confirmed' === $bill['status']) {
                    $confirmedAmount = $confirmedAmount->add($bill['amount']);
                }

                if ($billBalance->isNegative()) {
                    $refundAmount = $refundAmount->add($billBalance->absolute());
                }

                return $bill;
            });
        }
        //endregion Bills

        return array(
            'bills'            => $orderBills->toArray(),
            'order'            => $sampleOrder,
            'statuses'         => $billStatuses,
            'paid_amount'      => $paidAmount,
            'remain_amount'    => $sampleOrder['finalPrice']->subtract($confirmedAmount),
            'refund_amount'    => $refundAmount,
            'confirmed_amount' => $confirmedAmount,
        );
    }

    /**
     * Ensures that the user exists.
     *
     * @param int $itemId
     *
     * @deprecated
     */
    private function ensureUserExists(?int $userId): void
    {
        if (null === $userId) {
            return;
        }

        if (!$this->usersRepository->exist_user($userId)) {
            throw new NotFoundException("The user with ID '{$userId}' is not found.", static::USER_NOT_FOUND_ERROR);
        }
    }

    /**
     * Ensures that the item exists.
     *
     * @param int $itemId
     *
     * @deprecated
     */
    private function ensureItemExists(?int $itemId): void
    {
        if (null === $itemId) {
            return;
        }

        if (!$this->itemsRepository->item_exist($itemId)) {
            throw new NotFoundException("The item with ID '{$itemId}' is not found.", static::ITEM_NOT_FOUND_ERROR);
        }
    }
}
