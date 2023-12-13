<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmEventAttend extends LegacyEmail
{
    private string $userFullname;
    private array $event;
    private string $attendanceId;
    private string $attendEmail;

    public function __construct(string $userFullname, array $event, string $attendanceId, string $attendEmail, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirm_event_attend');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->event = $event;
        $this->attendanceId = $attendanceId;
        $this->attendEmail = $attendEmail;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[eventName]'   => $this->event['event_name'],
            '[mainBtnLink]' => __SITE_URL . 'cr_events/confirm_register/' . $this->event['id_event'] . '/' . $this->attendanceId . '/' . md5($this->attendEmail . $this->attendanceId),
        ]);

        parent::__construct($headers, $body);
    }
}
