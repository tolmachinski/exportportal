<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutB2b extends LegacyEmail
{
    private string $message;
    private string $imageLink;
    private array $request;

    public function __construct(string $message, array $request, string $imageLink, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_b2b');
        $this->markAsUnverified();
        $this->message = $message;
        $this->imageLink = $imageLink;
        $this->request = $request;

        $this->templateReplacements([
            '[message]'                 => $this->message,
            '[tableInfoTitle]'          => $this->request['b2b_title'],
            '[tableInfoDescription]'    => $this->request['b2b_message'],
            '[b2bLink]'                 => __SITE_URL . 'b2b/detail/' . strForURL($this->request['b2b_title']) . '-' . $this->request['id_request'],
            '[tableInfoImage]'          => $this->imageLink,
        ]);

        parent::__construct($headers, $body);
    }
}
