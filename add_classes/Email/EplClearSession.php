<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EplClearSession extends LegacyEmail
{
    private string $userName;
    private string $token;

    public function __construct(string $userName, string $token, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('epl_clear_session');
        $this->userName = $userName;
        $this->token = $token;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[resetLink]'   => __SHIPPER_URL . "login/clean_session/{$this->token}",
        ]);

        parent::__construct($headers, $body);
    }
}
