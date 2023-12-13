<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use League\Fractal\TransformerAbstract;

class ItemsCompilationsForBaiduTransformer extends TransformerAbstract
{
    public function transform(array $itemsCompilation)
    {
        return [
            'title'                   => $itemsCompilation['title'],
            'url'                     => $itemsCompilation['url'],
            'background_image_tablet' => getDisplayImageLink(['{FILE_NAME}' => $itemsCompilation['background_images']['tablet']], 'items_compilation.tablet'),
            'background_image_desktop'=> getDisplayImageLink(['{FILE_NAME}' => $itemsCompilation['background_images']['desktop']], 'items_compilation.desktop'),
            'items'                   => $itemsCompilation['items'], //TODO change to storage url when there will be path generator for products (items)
        ];
    }
}
