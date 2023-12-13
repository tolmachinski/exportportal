<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailUserAboutFreeFeaturedItems extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_user_about_free_featured_items');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[loginLink]'   => __SITE_URL . 'login?featured_items=1',
        ]);

        parent::__construct($headers, $body);
    }
}
