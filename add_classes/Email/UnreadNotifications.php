<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class UnreadNotifications extends LegacyEmail
{
    private string $userName;
    private string $messagesCount;
    private string $warningMessages;
    private string $noticeMessages;

    public function __construct(string $userName, string $messagesCount, string $warningMessages, string $noticeMessages, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('unread_notifications');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->messagesCount = $messagesCount;
        $this->warningMessages = $warningMessages;
        $this->noticeMessages = $noticeMessages;

        $this->templateReplacements([
            '[userName]'      => $this->userName,
            '[messagesCount]' => $this->messagesCount,
            '[siteUrl]'       => __SITE_URL,
            '[warning]'       => $this->warningMessages,
            '[notice]'        => $this->noticeMessages,
        ]);

        parent::__construct($headers, $body);
    }
}
