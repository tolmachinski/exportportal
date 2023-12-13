<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ChangeNotificationEmail extends LegacyEmail
{
    private string $userFullname;
    private string $code;

    public function __construct(string $userFullname, string $code, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('notification_email_change');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->code = $code;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[mainBtnLink]' => __SITE_URL . 'activate/change_info/notification_email/' . $this->code,
        ]);

        parent::__construct($headers, $body);
    }
}
