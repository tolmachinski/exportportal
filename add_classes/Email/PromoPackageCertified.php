<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class PromoPackageCertified extends LegacyEmail
{
    private string $userName;
    private string $token;

    public function __construct(string $userName, string $token, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('promo_package_certified');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->token = $token;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[mainBtnLink]'     => 'https://app.smartsheet.com/b/form/62a9789d0099410a82c5675fef8bf40c',
            '[downloadLink]'    => __SITE_URL . "download/promo_materials/{$this->token}",
        ]);

        parent::__construct($headers, $body);
    }
}
