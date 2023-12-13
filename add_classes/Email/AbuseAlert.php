<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class AbuseAlert extends LegacyEmail
{
    private string $userFullname;
    private string $type;
    private string $title;
    private string $url;
    private string $message;
    private string $abuse;
    private string $date;

    public function __construct(string $userFullname, string $type, string $title, string $url, string $message, string $abuse, string $date, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('moderation_abuse_alert');
        $this->userFullname = $userFullname;
        $this->type = $type;
        $this->title = $title;
        $this->url = $url;
        $this->message = $message;
        $this->abuse = $abuse;
        $this->date = $date;
        $this->templateReplacements([
            '[userName]'    => $this->userFullname,
            '[date]'        => $this->date,
            '[abuse]'       => $this->abuse,
            '[type]'        => $this->type,
            '[title]'       => $this->title,
            '[url]'         => $this->url,
            '[message]'     => $this->message,
            '[terms]'       => __SITE_URL . 'terms_and_conditions/tc_terms_of_use',
        ]);

        parent::__construct($headers, $body);
    }

    // /**
    //  * {@inheritDoc}
    //  */
    // public function getContext(): array
    // {
    //     return \array_replace_recursive(
    //         parent::getContext(),
    //         [
    //             'replacements' => [
    //                 '[userName]'    => $this->userFullname,
    //                 '[date]'        => $this->date,
    //                 '[abuse]'       => $this->abuse,
    //                 '[type]'        => $this->type,
    //                 '[title]'       => $this->title,
    //                 '[url]'         => $this->url,
    //                 '[message]'     => $this->message,
    //                 '[terms]'       => __SITE_URL . 'terms_and_conditions/tc_terms_of_use',
    //             ],
    //         ],
    //     );
    // }
}
