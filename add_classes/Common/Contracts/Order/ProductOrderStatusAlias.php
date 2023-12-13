<?php

declare(strict_types=1);

namespace App\Common\Contracts\Order;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self NEW_ORDER()
 * @method static self INVOICE_SENT_TO_BUYER()
 * @method static self INVOICE_CONFIRMED()
 * @method static self SHIPPER_ASSIGNED()
 * @method static self PAYMENT_PROCESSING()
 * @method static self ORDER_PAID()
 * @method static self PAYMENT_CONFIRMED()
 * @method static self PREPARING_FOR_SHIPPING()
 * @method static self SHIPPING_IN_PROGRESS()
 * @method static self SHIPPING_COMPLETED()
 * @method static self ORDER_COMPLETED()
 * @method static self LATE_PAYMENT()
 * @method static self CANCELED_BY_BUYER()
 * @method static self CANCELED_BY_SELLER()
 * @method static self CANCELED_BY_EP()
 * @method static self PURCHASE_ORDER()
 * @method static self PURCHASE_ORDER_CONFIRMED()
 * @method static self READY_FOR_PICKUP()
 */
final class ProductOrderStatusAlias extends EnumCase
{
    public const NEW_ORDER = 'new_order';
    public const INVOICE_SENT_TO_BUYER = 'invoice_sent_to_buyer';
    public const INVOICE_CONFIRMED = 'invoice_confirmed';
    public const SHIPPER_ASSIGNED = 'shipper_assigned';
    public const PAYMENT_PROCESSING = 'payment_processing';
    public const ORDER_PAID = 'order_paid';
    public const PAYMENT_CONFIRMED = 'payment_confirmed';
    public const PREPARING_FOR_SHIPPING = 'preparing_for_shipping';
    public const SHIPPING_IN_PROGRESS = 'shipping_in_progress';
    public const SHIPPING_COMPLETED = 'shipping_completed';
    public const ORDER_COMPLETED = 'order_completed';
    public const LATE_PAYMENT = 'late_payment';
    public const CANCELED_BY_BUYER = 'canceled_by_buyer';
    public const CANCELED_BY_SELLER = 'canceled_by_seller';
    public const CANCELED_BY_EP = 'canceled_by_ep';
    public const PURCHASE_ORDER = 'purchase_order';
    public const PURCHASE_ORDER_CONFIRMED = 'purchase_order_confirmed';
    public const READY_FOR_PICKUP = 'shipping_ready_for_pickup';

    /**
     * @param string $group
     * @return array
     */
    public static function getGroupStatuses(string $group): array
    {
        switch ($group) {
            case 'new':
                return [
                    static::NEW_ORDER(),
                    static::PURCHASE_ORDER(),
                    static::PURCHASE_ORDER_CONFIRMED(),
                    static::INVOICE_SENT_TO_BUYER(),
                    static::INVOICE_CONFIRMED(),
                ];

                break;
            case 'active':
                return [
                    static::SHIPPER_ASSIGNED(),
                    static::PAYMENT_PROCESSING(),
                    static::ORDER_PAID(),
                    static::PAYMENT_CONFIRMED(),
                    static::PREPARING_FOR_SHIPPING(),
                    static::SHIPPING_IN_PROGRESS(),
                    static::READY_FOR_PICKUP(),
                    static::SHIPPING_COMPLETED(),
                ];

                break;
            case 'passed':
                return [
                    static::ORDER_COMPLETED(),
                    static::LATE_PAYMENT(),
                    static::CANCELED_BY_BUYER(),
                    static::CANCELED_BY_SELLER(),
                    static::CANCELED_BY_EP(),
                ];

                break;
        }

        return [];
    }
}
