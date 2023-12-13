<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEp2WeeksAfter extends LegacyEmail
{
    private string $userName;
    private string $startDate;

    public function __construct(string $userName,string $startDate, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('2weeks_after_demo_webinar');
        $this->userName = $userName;
        $this->startDate = $startDate;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[registerLink]'    => __SITE_URL . 'register',
            '[date]'            => $this->startDate
        ]);

        parent::__construct($headers, $body);
    }
}
