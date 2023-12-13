<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class CrDeleteRequest extends LegacyEmail
{
    private string $userName;
    private string $notice;

    public function __construct(string $userName, string $notice, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('cr_delete_request');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->notice = $notice;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[notice]'      => nl2br(cleanOutput($this->notice)),
        ]);

        parent::__construct($headers, $body);
    }
}
