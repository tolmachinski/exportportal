<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class MatchmakingNewSeller extends LegacyEmail
{
    private string $userName;
    private string $countBuyers;

    public function __construct(string $userName, string $countBuyers, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('matchmaking_new_seller');
        $this->userName = $userName;
        $this->countBuyers = $countBuyers;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[countBuyers]' => $this->countBuyers,
            '[mainBtnLink]' => __SITE_URL . 'login',
        ]);

        parent::__construct($headers, $body);
    }
}
