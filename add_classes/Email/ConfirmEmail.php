<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmEmail extends LegacyEmail
{
    private string $userFullname;
    private string $token;

    public function __construct(string $userFullname, string $token, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirm_email');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->token = $token;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[mainBtnLink]' => __SITE_URL . "register/confirm_email/{$this->token}",
        ]);

        parent::__construct($headers, $body);
    }
}
