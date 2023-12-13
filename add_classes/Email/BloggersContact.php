<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class BloggersContact extends LegacyEmail
{
    private string $userName;
    private string $message;

    public function __construct(string $userName, string $message, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('contact_bloggers');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[message]'     => $this->message,
        ]);

        parent::__construct($headers, $body);
    }
}
