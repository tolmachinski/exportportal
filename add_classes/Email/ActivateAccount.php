<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ActivateAccount extends LegacyEmail
{
    private string $userFullname;
    private string $userGroup;

    public function __construct(string $userFullname, string $userGroup, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('account_activation');
        $this->userFullname = $userFullname;
        $this->userGroup = $userGroup;
        $sellerActivate = 'All of the items you have added to your profile are now active. If you would like to hide any of them from other users, you can change the item’s settings in the “My Items” page on your account. Please let us know if you require any assistance or further explanation.';

        $this->templateReplacements([
            '[userName]'   => $this->userFullname,
            '[mainBtnLink]'=> __SITE_URL . 'login',
            '[sellerInfo]' => 'Seller' === $this->userGroup ? $sellerActivate : '',
        ]);

        parent::__construct($headers, $body);
    }
}
