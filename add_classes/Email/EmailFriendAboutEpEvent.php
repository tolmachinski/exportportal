<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutEpEvent extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $event;
    private string $eventImageUrl;

    public function __construct(string $userName, string $message, array $event, string $eventImageUrl, Headers $headers = null, AbstractPart $body = null)
    {
        $this->userName = $userName;
        $this->message = $message;
        $this->event = $event;
        $this->eventImageUrl = $eventImageUrl;

        $this->templateName('email_friend_about_ep_event');
        $this->markAsUnverified();

        $this->templateReplacements([
            '[tableInfoDescription]'    => cut_str_with_dots(cleanOutput($event['short_description'], 200)),
            '[tableInfoTitle]'          => cleanOutput($event['title']),
            '[tableInfoImage]'          => $eventImageUrl,
            '[eventTitle]'              => cleanOutput($event['title']),
            '[userName]'                => $userName,
            '[eventUrl]'                => getEpEventDetailUrl($event),
            '[message]'                 => $message,
        ]);

        parent::__construct($headers, $body);
    }
}
