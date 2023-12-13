<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutLibrary extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $company;
    private array $document;

    public function __construct(string $userName, string $message, array $company, array $document, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_library');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->company = $company;
        $this->document = $document;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[libraryLink]'             => getCompanyUrl($this->company) . '/document/' . $this->document['id_file'],
            '[tableInfoTitle]'          => $this->document['title_file'],
            '[tableInfoDescription]'    => truncWords($this->document['description_file'], 30),
            '[tableInfoImage]'          => __IMG_URL . 'public/img/icons_file/48/' . $this->document['extension_file'] . '.png',
        ]);

        parent::__construct($headers, $body);
    }
}
