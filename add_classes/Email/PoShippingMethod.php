<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class PoShippingMethod extends LegacyEmail
{
    private string $userName;
    private string $orderNumber;
    private array $shipmentTypeDetail;

    public function __construct(string $userName, string $orderNumber, array $shipmentTypeDetail, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('po_shipping_method');
        $this->userName = $userName;
        $this->orderNumber = $orderNumber;
        $this->shipmentTypeDetail = $shipmentTypeDetail;

        $this->templateReplacements([
            '[userName]'                    => $this->userName,
            '[orderNumber]'                 => $this->orderNumber,
            '[shippingMethod]'              => $this->shipmentTypeDetail['type_name'],
            '[shippingMethoDescription]'    => $this->shipmentTypeDetail['type_description'],
        ]);

        parent::__construct($headers, $body);
    }
}
