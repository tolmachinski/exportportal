<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class OutOfStockItem extends LegacyEmail
{
    private string $userName;
    private array $item;

    public function __construct(string $userName, array $item, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('out_of_stock_item');
        $this->userName = $userName;
        $this->item = $item;

        $this->templateReplacements([
            '[userName]'      => $this->userName,
            '[itemLink]'      => __SITE_URL . 'item/' . strForURL($this->item['title']) . '-' . $this->item['id'],
            '[itemName]'      => cleanOutput($this->item['title']),
        ]);

        parent::__construct($headers, $body);
    }
}
