<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailUserAboutBill extends LegacyEmail
{
    private string $userFullname;
    private string $billUrl;
    private string $message;

    public function __construct(string $userFullname, string $message, string $billUrl, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_user_about_bill');
        $this->userFullname = $userFullname;
        $this->billUrl = $billUrl;
        $this->message = $message;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[message]'     => $this->message,
            '[mainBtnLink]' => $this->billUrl,
        ]);

        parent::__construct($headers, $body);
    }
}
