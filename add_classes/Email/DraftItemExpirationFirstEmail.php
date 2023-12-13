<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DraftItemExpirationFirstEmail extends LegacyEmail
{
    private string $userName;
    private string $counter;
    private string $expireDays;
    private string $date;
    private string $idRequest;

    public function __construct(string $userName, string $counter, string $expireDays, string $date, string $idRequest, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('draft_item_expiration_first_email');
        $this->userName = $userName;
        $this->counter = $counter;
        $this->expireDays = $expireDays;
        $this->date = $date;
        $this->idRequest = $idRequest;

        $this->templateReplacements([
            '[userName]'    => $this->userName,
            '[counter]'     => $this->counter,
            '[days]'        => $this->expireDays,
            '[mainBtnLink]' => __SITE_URL . 'items/my?expire=' . $this->date,
            '[requestLink]' => __SITE_URL . 'items/my?request=' . $this->idRequest,
        ]);

        parent::__construct($headers, $body);
    }
}
