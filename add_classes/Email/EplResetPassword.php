<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EplResetPassword extends LegacyEmail
{
    private string $userName;
    private string $code;

    public function __construct(string $userName, string $code, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('epl_reset_password');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->code = $code;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[resetLink]'   => __SHIPPER_URL . "authenticate/reset/{$this->code}",
        ]);

        parent::__construct($headers, $body);
    }
}
