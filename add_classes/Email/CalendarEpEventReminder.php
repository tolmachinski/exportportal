<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class CalendarEpEventReminder extends LegacyEmail
{
    private string $userName;
    private string $eventUrl;
    private int $remainingDays;

    public function __construct(string $userName, string $eventUrl, int $remainingDays, Headers $headers = null, AbstractPart $body = null)
    {
        $this->userName = $userName;
        $this->eventUrl = $eventUrl;
        $this->remainingDays = $remainingDays;

        $this->templateName('calendar_ep_event_reminder');
        $this->templateReplacements([
            '[userName]'      => $userName,
            '[eventName]'     => $eventUrl,
            '[remainingDays]' => $remainingDays,
        ]);

        parent::__construct($headers, $body);
    }
}
