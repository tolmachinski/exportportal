<?php

declare(strict_types=1);

namespace App\Common\Contracts\Order;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self PO()
 * @method static self OFFER()
 * @method static self BASKET()
 * @method static self INQUIRY()
 * @method static self ESTIMATE()
 */
final class ProductOrderType extends EnumCase
{
    public const PO = 'po';
    public const OFFER = 'offer';
    public const BASKET = 'basket';
    public const INQUIRY = 'inquiry';
    public const ESTIMATE = 'estimate';
}
