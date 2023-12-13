<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class DownloadableMaterialsShare extends LegacyEmail
{
    private string $title;
    private string $slug;

    public function __construct(string $title, string $slug, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('downloadable_materials_share');
        $this->markAsUnverified();
        $this->title = $title;
        $this->slug = $slug;

        $this->templateReplacements([
            '[title]'   => $this->title,
            '[link]'    => __SITE_URL . "downloadable_materials/details/{$this->slug}",
        ]);

        parent::__construct($headers, $body);
    }
}
