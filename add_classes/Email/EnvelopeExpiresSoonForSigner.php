<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EnvelopeExpiresSoonForSigner extends LegacyEmail
{
    private string $userName;
    private string $orderSenderId;

    public function __construct(string $userName, string $orderSenderId, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('envelope_expires_soon_for_signer');
        $this->userName = $userName;
        $this->orderSenderId = $orderSenderId;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[mainBtnLink]' => __SITE_URL . 'order_documents?document=' . $this->orderSenderId,
        ]);

        parent::__construct($headers, $body);
    }
}
