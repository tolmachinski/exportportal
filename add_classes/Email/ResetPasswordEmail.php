<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ResetPasswordEmail extends LegacyEmail
{
    private string $userFullname;
    private string $resetPasswordUrl;

    public function __construct(string $userFullname, string $resetPasswordUrl, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('authenticate_reset_password');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->resetPasswordUrl = $resetPasswordUrl;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[mainBtnLink]' => $this->resetPasswordUrl,
        ]);

        parent::__construct($headers, $body);
    }
}
