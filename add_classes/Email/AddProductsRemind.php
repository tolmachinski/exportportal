<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class AddProductsRemind extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('add_products_remind');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'      => $this->userName,
            '[addItemUrl]'    => __SITE_URL . 'items/my?popup_add=open',
        ]);

        parent::__construct($headers, $body);
    }
}
