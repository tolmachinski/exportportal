<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class ShareItem extends LegacyEmail
{
    private string $message;
    private array $item;
    private string $photoName;
    private string $userName;

    public function __construct(string $userName, string $message, array $item, string $photoName, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('share_item');
        $this->message = $message;
        $this->item = $item;
        $this->photoName = $photoName;
        $this->userName = $userName;

        $this->templateReplacements([
            '[userName]'        => $this->userName,
            '[message]'         => $this->message,
            '[infoLink]'        => makeItemUrl($this->item['id'], $this->item['title']),
            '[tableInfoTitle]'  => $this->item['title'],
            '[tableInfoImage]'  => getDisplayImageLink(['{ID}' => $this->item['id'], '{FILE_NAME}' => $this->photoName], 'items.main', ['thumb_size' => 1]),
        ]);

        parent::__construct($headers, $body);
    }
}
