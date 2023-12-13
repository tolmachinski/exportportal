<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order;

interface DocumentMakerInterface
{
    /**
     * Makes the document for order.
     */
    public function make(int $orderId, string $name): string;
}
