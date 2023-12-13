<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutPicture extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $company;
    private array $picture;

    public function __construct(
        string $userName,
        string $message,
        array $company,
        array $picture,
        Headers $headers = null,
        AbstractPart $body = null,
        string $imageLink = null
    )
    {
        $this->templateName('email_friend_about_picture');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->company = $company;
        $this->picture = $picture;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[pictureLink]'             => getCompanyUrl($this->company) . '/picture/' . strForURL($this->picture['title_photo']) . '-' . $this->picture['id_photo'],
            '[tableInfoTitle]'          => $this->picture['title_photo'],
            '[tableInfoDescription]'    => truncWords($this->picture['description_photo'], 50),
            '[tableInfoImage]'          => $imageLink,
        ]);

        parent::__construct($headers, $body);
    }
}
