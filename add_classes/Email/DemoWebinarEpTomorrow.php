<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEpTomorrow extends LegacyEmail
{
    private string $userName;
    private string $date;
    private string $time;
    private string $webinarLink;

    public function __construct(string $userName, string $date, string $time, string $webinarLink, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('tomorrow_ep_webinar_demo');
        $this->userName = $userName;
        $this->date = $date;
        $this->time = $time;
        $this->webinarLink = $webinarLink;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[date]'        => $this->date,
            '[time]'        => $this->time,
            '[webinarLink]' => $this->webinarLink,
        ]);

        parent::__construct($headers, $body);
    }
}
