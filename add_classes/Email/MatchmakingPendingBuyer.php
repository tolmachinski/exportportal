<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class MatchmakingPendingBuyer extends LegacyEmail
{
    private string $userName;
    private string $countSellers;
    private string $countItems;

    public function __construct(string $userName, string $countSellers, string $countItems, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('matchmaking_pending_buyer');
        $this->userName = $userName;
        $this->countSellers = $countSellers;
        $this->countItems = $countItems;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[countCompanies]'  => $this->countSellers,
            '[countItems]'      => $this->countItems,
            '[mainBtnLink]'     => __SITE_URL . 'search?recommended=1',
            '[loginUrl]'        => __SITE_URL . 'login',
        ]);

        parent::__construct($headers, $body);
    }
}
