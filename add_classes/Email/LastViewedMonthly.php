<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class LastViewedMonthly extends LegacyEmail
{
    private string $userName;
    private string $links;

    public function __construct(string $userName, string $links, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('last_viewed_monthly');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->links = $links;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[linksToItems]'    => $this->links,
        ]);

        parent::__construct($headers, $body);
    }
}
