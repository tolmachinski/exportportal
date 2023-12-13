<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutShipperCompany extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $shipper;

    public function __construct(string $userName, string $message, array $shipper, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_shipper_company');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->shipper = $shipper;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[companyLink]'             => getShipperURL($this->shipper),
            '[tableInfoTitle]'          => $this->shipper['co_name'],
            '[tableInfoDescription]'    => $this->shipper['description'],
            '[tableInfoImage]'          => getDisplayImageLink(['{ID}' => $this->shipper['id'], '{FILE_NAME}' => $this->shipper['logo']], 'shippers.main', ['thumb_size' => 1]),
        ]);

        parent::__construct($headers, $body);
    }
}
