<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class OutOfStockBackInStock extends LegacyEmail
{
    private string $userName;
    private string $companyName;
    private array $item;

    public function __construct(string $userName, string $companyName, array $item, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('out_of_stock_back_in_stock');
        $this->userName = $userName;
        $this->companyName = $companyName;
        $this->item = $item;

        $this->templateReplacements([
            '[userName]'      => $this->userName,
            '[sellerCompany]' => $this->companyName,
            '[itemLink]'      => __SITE_URL . 'item/' . strForURL($this->item['title']) . '-' . $this->item['id'],
            '[itemName]'      => cleanOutput($this->item['title']),
        ]);

        parent::__construct($headers, $body);
    }
}
