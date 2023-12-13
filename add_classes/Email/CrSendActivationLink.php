<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class CrSendActivationLink extends LegacyEmail
{
    private string $userName;
    private string $groupName;
    private string $code;
    private string $password;

    public function __construct(string $userName, string $groupName, string $code, string $password, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('cr_send_activation_link');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->groupName = $groupName;
        $this->code = $code;
        $this->password = $password;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[groupName]'       => $this->groupName,
            '[mainBtnLink]'     => __SITE_URL . "activate/cr_account/{$this->code}",
            '[password]'        => $this->password,
            '[baGuideUrl]'      => __SITE_URL . 'public/user_guide/ba',
        ]);

        parent::__construct($headers, $body);
    }
}
