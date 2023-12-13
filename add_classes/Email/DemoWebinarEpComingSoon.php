<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEpComingSoon extends LegacyEmail
{
    private string $userName;
    private string $date;
    private string $time;

    public function __construct(string $userName, string $date, string $time, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('coming_soon_ep_webinar_demo');
        $this->userName = $userName;
        $this->date = $date;
        $this->time = $time;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[date]'        => $this->date,
            '[time]'        => $this->time,
        ]);

        parent::__construct($headers, $body);
    }
}
