<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class CompleteProfileRemind extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('complete_profile_remind');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[mainBtnLink]' => __SITE_URL . 'login',
        ]);

        parent::__construct($headers, $body);
    }
}
