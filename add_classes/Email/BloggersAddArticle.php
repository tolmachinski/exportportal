<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class BloggersAddArticle extends LegacyEmail
{
    public function __construct(Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('bloggers_add_article');
        $this->markAsUnverified();

        $this->templateReplacements([
            '[blogUrl]' => __BLOG_URL,
        ]);

        parent::__construct($headers, $body);
    }
}
