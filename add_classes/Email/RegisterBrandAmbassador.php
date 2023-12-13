<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class RegisterBrandAmbassador extends LegacyEmail
{
    public function __construct(Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('register_brand_ambassador');
        $this->markAsUnverified();

        $this->templateReplacements([
            '[learnMore]'   => __SITE_URL . 'learn_more',
            '[email]'       => 'mailto:' . config('epcountryambassador_email'),
            '[emailName]'   => config('epcountryambassador_email'),
        ]);

        parent::__construct($headers, $body);
    }
}
