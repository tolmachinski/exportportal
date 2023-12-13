<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EnvelopeExpiresSoonForSender extends LegacyEmail
{
    private string $userName;
    private string $orderNumber;
    private string $idOrder;
    private string $dueDate;

    public function __construct(string $userName, string $orderNumber, string $idOrder, string $dueDate, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('envelope_expires_soon_for_sender');
        $this->userName = $userName;
        $this->orderNumber = $orderNumber;
        $this->idOrder = $idOrder;
        $this->dueDate = $dueDate;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[orderNumber]' => $this->orderNumber,
            '[orderLink]'   => __SITE_URL . 'order/my/order_number/' . $this->idOrder,
            '[dueDate]'     => $this->dueDate,
        ]);

        parent::__construct($headers, $body);
    }
}
