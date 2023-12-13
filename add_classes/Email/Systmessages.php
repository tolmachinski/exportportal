<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class Systmessages extends LegacyEmail
{
    private string $userName;
    private string $messageDate;
    private string $messageTitle;
    private string $additionalContent;

    public function __construct(string $userName, string $messageDate, string $messageTitle, string $additionalContent, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_systmessages');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->messageDate = $messageDate;
        $this->messageTitle = $messageTitle;
        $this->additionalContent = $additionalContent;

        $this->templateReplacements([
            '[userName]'          => $this->userName,
            '[messageDate]'       => $this->messageDate,
            '[messageTitle]'      => $this->messageTitle,
            '[additionalContent]' => $this->additionalContent,
        ]);

        parent::__construct($headers, $body);
    }
}
