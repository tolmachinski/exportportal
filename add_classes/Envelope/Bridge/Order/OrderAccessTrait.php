<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order;

use App\Common\Exceptions\AccessDeniedException;

trait OrderAccessTrait
{
    /**
     * Check if sender has access to the order.
     *
     * @throws AccessDeniedException if sender has no access to the order
     */
    private function assertSenderHasAccessToOrder(int $senderId, array $order): void
    {
        if (!in_array($senderId, [(int) $order['id_buyer'], (int) $order['id_seller'], (int) $order['id_shipper']])) {
            throw new AccessDeniedException('The sender cannot access this order');
        }
    }
}
