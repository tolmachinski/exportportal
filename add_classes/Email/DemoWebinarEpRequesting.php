<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEpRequesting extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('requesting_a_demo');
        $this->markAsUnverified();
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[guidesLink]'  => __SITE_URL . 'user_guide',
            '[videosLink]'  => 'https://youtu.be/KTH5rCpoStw',
        ]);

        parent::__construct($headers, $body);
    }
}
