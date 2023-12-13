<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutCompanyUpdates extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $company;
    private string $description;
    private string $imageUrl;

    public function __construct(string $userName, string $message, array $company, string $description, string $imageUrl, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_company_updates');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->company = $company;
        $this->description = $description;
        $this->imageUrl = $imageUrl;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[companyUrl]'              => getCompanyUrl($this->company) . '/updates',
            '[tableInfoDescription]'    => $this->description,
            '[tableInfoImage]'          => $this->imageUrl,
            '[tableInfoTitle]'          => 'Company update',
        ]);

        parent::__construct($headers, $body);
    }
}
