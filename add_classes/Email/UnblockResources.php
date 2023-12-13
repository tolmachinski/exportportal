<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class UnblockResources extends LegacyEmail
{
    private string $userFullname;
    private string $type;
    private string $typeHeader;
    private string $title;

    public function __construct(string $userFullname, string $typeHeader, string $type, string $title, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('moderation_unblock_resource');
        $this->userFullname = $userFullname;
        $this->typeHeader = $typeHeader;
        $this->type = $type;
        $this->title = $title;

        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[typeHeader]'  => $this->typeHeader,
            '[type]'        => $this->type,
            '[title]'       => $this->title,
        ]);

        parent::__construct($headers, $body);
    }
}
