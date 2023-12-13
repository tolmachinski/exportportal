<?php

declare(strict_types=1);

namespace App\Email;

use AWS\CRT\HTTP\Headers;
use Symfony\Component\Mime\Part\AbstractPart;
use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;

/**
 * Construct template for Droplist price changed email notification
 */
final class ChangeDroplistPrice extends LegacyEmail
{

    private string $productLink;
    private string $productName;
    private string $oldPrice;
    private string $newPrice;

    public function __construct(string $productLink, string $productName, string $oldPrice, string $newPrice, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('changed_droplist_price');
        $this->productLink  = $productLink;
        $this->productName  = $productName;
        $this->oldPrice     = $oldPrice;
        $this->newPrice     = $newPrice;

        $this->templateReplacements([
            '[productLink]' => $this->productLink,
            '[productName]' => $this->productName,
            '[oldPrice]'    => $this->oldPrice,
            '[newPrice]'    => $this->newPrice,
        ]);

        parent::__construct($headers, $body);
    }


}