<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class StayActiveOnEp extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('stay_active_on_ep');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[mainBtnLink]' => __SITE_URL . 'login',
        ]);

        parent::__construct($headers, $body);
    }
}
