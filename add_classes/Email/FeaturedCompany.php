<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class FeaturedCompany extends LegacyEmail
{
    private string $userFullname;

    public function __construct(string $userFullname, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('directory_change_featured_status');
        $this->userFullname = $userFullname;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
        ]);

        parent::__construct($headers, $body);
    }
}
