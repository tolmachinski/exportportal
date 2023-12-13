<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEpThanksForParticipatedBuyers extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('thanks_for_participated_ep_demo_webinar_buyers');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'            => $this->userName,
            '[featuredItemsLink]'   => __SITE_URL . 'items/featured',
        ]);

        parent::__construct($headers, $body);
    }
}
