<?php

namespace App\Common\Traits\Items;

use Money\Money;

trait FeatureItemRequestTrait
{
    /**
     * Add and featured item request.
     *
     * @param int    $itemId
     * @param string $itemTitle
     * @param Money  $itemPrice
     * @param int    $userId
     * @param string $userName
     *
     * @return bool
     */
    protected function addFeatureItemRequest($itemId, $itemTitle, Money $itemPrice, $userId, $userName)
    {
        $requestId = model('items')->set_feature_request(array(
            'id_item'     => $itemId,
            'status'      => 'init',
            'price'       => moneyToDecimal($itemPrice),
            'create_date' => date('Y-m-d'),
            'notice'      => json_encode(array(
                'add_by'   => $userName,
                'notice'   => 'The feature was initiated.',
                'add_date' => formatDate(date('Y-m-d H:i:s')),
            )),
        ));

        if (
            empty($requestId)
            || empty($billId = $this->addFeatureItemBill($requestId, $itemPrice, $itemTitle, $userId))
        ) {
            return false;
        }

        $this->sendFeatureRequestNotification($billId, $itemId, $itemTitle, $userId);
        $this->updateFeatureItemStatistic($userId);
    }

    /**
     * Updates user's statisitc.
     *
     * @param int $userId
     */
    private function updateFeatureItemStatistic($userId)
    {
        model('user_statistic')->set_users_statistic(array($userId => array('total_featured_items' => 1)));
    }

    /**
     * Adds a bill for the item feature request.
     *
     * @param int    $requestId
     * @param Money  $price
     * @param string $itemTitle
     * @param int    $userId
     */
    private function addFeatureItemBill($requestId, $price, $itemTitle, $userId)
    {
        if (
            empty($billId = model('user_bills')->set_user_bill(array(
                'id_user'          => $userId,
                'id_item'          => $requestId,
                'id_type_bill'     => 3,
                'bill_description' => "This bill is for payment of feature item - \"{$itemTitle}\" request.",
                'balance'          => moneyToDecimal($price),
                'total_balance'    => moneyToDecimal($price),
                'due_date'         => date('Y-m-d', strtotime(sprintf('+%s days', config('item_featured_bill_period')))),
            )))
        ) {
            return false;
        }

        return $billId;
    }

    /**
     * Sends notification about added featured item request.
     *
     * @param int    $billId
     * @param int    $itemId
     * @param string $itemTitle
     * @param int    $userId
     */
    private function sendFeatureRequestNotification($billId, $itemId, $itemTitle, $userId)
    {
        model('notify')->send_notify([
            'systmess'      => true,
            'mess_code'     => 'request_feature_item',
            'id_item'       => $billId,
            'id_users'      => [$userId],
            'replace'       => [
                '[LINK]'       => getUrlForGroup('billing/my'),
                '[BILL_ID]'    => orderNumber($billId),
                '[ITEM_TITLE]' => $itemTitle,
                '[ITEM_LINK]'  => getUrlForGroup('item/' . strForURL($itemTitle) . '-' . $itemId),
                '[BILL_LINK]'  => getUrlForGroup("billing/my/bill/{$billId}"),
            ],
        ]);
    }
}
