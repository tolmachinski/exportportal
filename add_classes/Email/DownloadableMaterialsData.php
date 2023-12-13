<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DownloadableMaterialsData extends LegacyEmail
{
    private string $userName;
    private string $title;
    private string $slug;
    private string $signature;

    public function __construct(string $userName, string $title, string $slug, string $signature, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('downloadable_materials_data');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->title = $title;
        $this->slug = $slug;
        $this->signature = $signature;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[title]'           => $this->title,
            '[titleLink]'       => __SITE_URL . "downloadable_materials/details/{$this->slug}",
            '[mainBtnLink]'     => __SITE_URL . "downloadable_materials/details/{$this->slug}/{$this->signature}",
        ]);

        parent::__construct($headers, $body);
    }
}
