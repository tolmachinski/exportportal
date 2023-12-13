<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmFeedback extends LegacyEmail
{
    private string $userFullname;
    private string $companyName;
    private string $companyUrl;
    private string $confirmCode;

    public function __construct(string $userFullname, string $companyName, string $companyUrl, string $confirmCode, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirm_feedback');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->companyName = $companyName;
        $this->companyUrl = $companyUrl;
        $this->confirmCode = $confirmCode;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[companyName]' => $this->companyName,
            '[companyUrl]'  => $this->companyUrl,
            '[mainBtnLink]' => $this->companyUrl . '?type=feedback&confirm=' . $this->confirmCode,
        ]);

        parent::__construct($headers, $body);
    }
}
