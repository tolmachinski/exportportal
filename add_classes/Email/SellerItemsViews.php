<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class SellerItemsViews extends LegacyEmail
{
    private string $userName;
    private int $countViews;
    private string $itemsList;

    public function __construct(string $userName, int $countViews, array $mostViewedItems, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('seller_items_views');
        $this->userName = $userName;
        $this->countViews = $countViews;

        $itemsList = [];
        foreach ($mostViewedItems as $item) {
            $itemsList[] = sprintf(
                <<<LIST
                    <li><a href="%s">%s</a></li>
                LIST,
                makeItemUrl($item['id'], $item['title']),
                cleanOutput($item['title'])
            );
        }

        $this->itemsList = '<ul>' . implode('', $itemsList) . '</ul>';

        $this->templateReplacements([
            '[userName]'            => $this->userName,
            '[countViews]'          => $this->countViews,
            '[mainBtnLink]'        => __SITE_URL . 'items/my?popup_add=open',
            '[itemsList]'           => $this->itemsList,
        ]);

        parent::__construct($headers, $body);
    }
}
