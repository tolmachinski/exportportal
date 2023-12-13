<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutNews extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $company;
    private array $newsInfo;

    public function __construct(
        string $userName,
        string $message,
        array $company,
        array $newsInfo,
        Headers $headers = null,
        AbstractPart $body = null,
        string $imageLink = null
    )
    {
        $this->templateName('email_friend_about_news');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->company = $company;
        $this->newsInfo = $newsInfo;
        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[newsLink]'                => getCompanyUrl($this->company) . '/view_news/' . strForURL($this->newsInfo['title_news']) . '-' . $this->newsInfo['id_news'],
            '[tableInfoTitle]'          => $this->newsInfo['title_news'],
            '[tableInfoDescription]'    => truncWords(strip_tags($this->newsInfo['text_news']), 50),
            '[tableInfoImage]'          => $imageLink,
        ]);

        parent::__construct($headers, $body);
    }
}
