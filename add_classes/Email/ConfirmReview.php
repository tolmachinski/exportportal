<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ConfirmReview extends LegacyEmail
{
    private string $userFullname;
    private array $itemInfo;
    private array $companyInfo;
    private string $confirmCode;

    public function __construct(string $userFullname, array $itemInfo, array $companyInfo, string $confirmCode, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('confirm_review');
        $this->markAsUnverified();
        $this->userFullname = $userFullname;
        $this->itemInfo = $itemInfo;
        $this->companyInfo = $companyInfo;
        $this->confirmCode = $confirmCode;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[itemUrl]'     => makeItemUrl($this->itemInfo['id'], $this->itemInfo['title']),
            '[itemTitle]'   => $this->itemInfo['title'],
            '[mainBtnLink]' => getCompanyURL($this->companyInfo) . '?type=review&confirm=' . $this->confirmCode,
        ]);

        parent::__construct($headers, $body);
    }
}
