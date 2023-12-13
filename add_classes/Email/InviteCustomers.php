<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class InviteCustomers extends LegacyEmail
{
    private array $company;
    private string $message;

    public function __construct(array $company, string $message, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('invite_customers');
        $this->markAsUnverified();
        $this->company = $company;
        $this->message = $message;

        $this->templateReplacements([
            '[companyName]' => $this->company['name_company'],
            '[companyUrl]'  => getCompanyURL($this->company),
            '[mainBtnLink]' => get_static_url('register/index'),
            '[message]'     => cleanInput($this->message),
        ]);

        parent::__construct($headers, $body);
    }
}
