<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class SubscribeToNewsletter extends LegacyEmail
{
    public function __construct(Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('subscribe_to_newsletter');
        $this->markAsUnverified();
        $this->templateReplacements([]);
        parent::__construct($headers, $body);
    }
}
