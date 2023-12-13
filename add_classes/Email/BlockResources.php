<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class BlockResources extends LegacyEmail
{
    private string $userFullname;
    private string $abuse;
    private string $type;
    private string $typeHeader;
    private string $title;

    public function __construct(string $userFullname, string $abuse, string $typeHeader, string $type, string $title, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('moderation_block_resource');
        $this->userFullname = $userFullname;
        $this->abuse = $abuse;
        $this->type = $type;
        $this->typeHeader = $typeHeader;
        $this->title = $title;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[typeHeader]'  => $this->typeHeader,
            '[type]'        => $this->type,
            '[title]'       => $this->title,
            '[abuse]'       => $this->abuse,
            '[termsLink]'   => __SITE_URL . 'terms_and_conditions/tc_terms_of_use',
        ]);

        parent::__construct($headers, $body);
    }
}
