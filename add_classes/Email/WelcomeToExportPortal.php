<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class WelcomeToExportPortal extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('welcome_to_export_portal');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
        ]);

        parent::__construct($headers, $body);
    }
}
