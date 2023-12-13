<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class StartUsingEpAgain extends LegacyEmail
{
    private string $userName;

    public function __construct(string $userName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('start_using_ep_again');
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'            => $this->userName,
            '[termsConditionsLink]' => __SITE_URL . 'terms_and_conditions/tc_terms_of_use',
        ]);

        parent::__construct($headers, $body);
    }
}
