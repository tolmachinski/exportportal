<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmSubscription extends LegacyEmail
{
    private string $token;

    public function __construct(string $token, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirm_subscription');
        $this->markAsUnverified();
        $this->token = $token;

        $this->templateReplacements([
            '[mainBtnLink]'    => __SITE_URL . 'subscribe/confirm_subscription/' . $this->token,
        ]);

        parent::__construct($headers, $body);
    }
}
