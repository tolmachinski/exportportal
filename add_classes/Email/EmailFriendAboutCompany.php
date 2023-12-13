<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutCompany extends LegacyEmail
{
    private string $userFullname;
    private string $message;
    private string $imageLink;
    private array $company;

    public function __construct(string $userFullname, string $message, array $company, string $imageLink, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_company');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->message = $message;
        $this->imageLink = $imageLink;
        $this->company = $company;

        $this->templateReplacements([
            '[userName]'                => $this->userFullname,
            '[message]'                 => $this->message,
            '[companyUrl]'              => getCompanyUrl($this->company),
            '[tableInfoDescription]'    => cut_str_with_dots(cleanOutput(cleanInput($this->company['description_company']), 200)),
            '[tableInfoTitle]'          => $this->company['name_company'],
            '[tableInfoImage]'          => $this->imageLink,
        ]);

        parent::__construct($headers, $body);
    }
}
