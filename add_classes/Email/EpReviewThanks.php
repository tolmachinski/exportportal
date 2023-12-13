<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EpReviewThanks extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('thanks_for_ep_review');
        $this->markAsUnverified();
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
        ]);

        parent::__construct($headers, $body);
    }
}
