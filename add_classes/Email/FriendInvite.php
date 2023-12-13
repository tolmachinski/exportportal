<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class FriendInvite extends LegacyEmail
{
    private string $message;
    private string $userName;

    public function __construct($userName, $message, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('friend_invite');
        $this->markAsUnverified();
        $this->message = $message;
        $this->userName = $userName;

        $this->templateReplacements([
            '[message]'     => $this->message,
            '[userName]'    => $this->userName,
        ]);

        parent::__construct($headers, $body);
    }
}
