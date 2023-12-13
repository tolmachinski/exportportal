<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutVideo extends LegacyEmail
{
    private string $userName;
    private string $message;
    private string $imageLink;
    private array $company;
    private array $video;

    public function __construct(string $userName, string $message, array $company, array $video, string $imageLink, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_video');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->company = $company;
        $this->video = $video;
        $this->imageLink = $imageLink;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[videoLink]'               => getCompanyUrl($this->company) . '/video/' . strForURL($this->video['title_video']) . '-' . $this->video['id_video'],
            '[tableInfoDescription]'    => truncWords($this->video['description_video'], 50),
            '[tableInfoTitle]'          => $this->video['title_video'],
            '[tableInfoImage]'          => $this->imageLink,
        ]);

        parent::__construct($headers, $body);
    }
}
