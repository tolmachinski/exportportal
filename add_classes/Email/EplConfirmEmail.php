<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EplConfirmEmail extends LegacyEmail
{
    private string $userName;
    private string $token;

    public function __construct(string $userName, string $token, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('epl_confirm_email');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->token = $token;

        $this->templateReplacements([
            '[userName]'      => $this->userName,
            '[confirmLink]'   => __CURRENT_SUB_DOMAIN_URL . "register/confirm_email/{$this->token}",
        ]);

        parent::__construct($headers, $body);
    }
}
