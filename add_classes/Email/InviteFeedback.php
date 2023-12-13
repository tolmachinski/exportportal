<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class InviteFeedback extends LegacyEmail
{
    private array $company;
    private string $hash;
    private string $message;

    public function __construct(array $company, string $hash, string $message, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('invite_feedback');
        $this->markAsUnverified();
        $this->company = $company;
        $this->hash = $hash;
        $this->message = $message;

        $companyUrl = getCompanyURL($this->company);

        $this->templateReplacements([
            '[companyUrl]'  => $companyUrl,
            '[companyName]' => $this->company['name_company'],
            '[feedbackUrl]' => $companyUrl . '?type=feedback&external=' . $this->hash,
            '[mainBtnLink]' => $companyUrl . '?type=review&external=' . $this->hash,
            '[message]'     => $this->message,
        ]);

        parent::__construct($headers, $body);
    }
}
