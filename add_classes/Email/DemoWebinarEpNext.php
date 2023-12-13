<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DemoWebinarEpNext extends LegacyEmail
{
    private string $userName;
    private string $date;
    private string $time;
    private string $hash;

    public function __construct(string $userName, string $date, string $time, string $hash, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('next_demo_webinar');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->date = $date;
        $this->time = $time;
        $this->hash = $hash;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[date]'        => $this->date,
            '[time]'        => $this->time,
            '[mainBtnLink]' => __SITE_URL . 'about/?request=' . $this->hash,
        ]);

        parent::__construct($headers, $body);
    }
}
