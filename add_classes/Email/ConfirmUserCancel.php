<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmUserCancel extends LegacyEmail
{
    private string $userFullname;
    private string $confirmCode;

    public function __construct(string $userFullname, string $confirmCode, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirmation_user_cancel');
        $this->userFullname = $userFullname;
        $this->confirmCode = $confirmCode;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[mainBtnLink]' => __SITE_URL . "account/confirm-cancelation/{$this->confirmCode}",
        ]);

        parent::__construct($headers, $body);
    }
}
