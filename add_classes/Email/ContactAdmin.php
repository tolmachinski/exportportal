<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ContactAdmin extends LegacyEmail
{
    private string $userName;
    private string $message;
    private string $email;
    private string $phoneNumber;

    public function __construct(string $userName, string $email, string $phoneNumber, string $message, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('contact_admin');
        $this->userName = $userName;
        $this->message = $message;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[contactEmail]'    => $this->email,
            '[userPhoneNumber]' => $this->phoneNumber,
            '[message]'         => $this->message,
        ]);

        parent::__construct($headers, $body);
    }
}
