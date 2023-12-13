<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ShareCrEvent extends LegacyEmail
{
    private string $userName;
    private string $message;
    private string $eventLink;
    private array $event;

    public function __construct(string $userName, string $message, string $eventLink, array $event, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('share_cr_event');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->eventLink = $eventLink;
        $this->event = $event;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[link]'                    => $this->eventLink,
            '[tableInfoImage]'          => __IMG_URL . getImage('public/img/cr_event_images/' . $this->event['id_event'] . '/' . $this->event['event_image'], 'public/img/no_image/no-image-166x138.png'),
            '[tableInfoTitle]'          => $this->event['event_name'],
            '[tableInfoDescription]'    => $this->event['event_short_description'],
        ]);

        parent::__construct($headers, $body);
    }
}
