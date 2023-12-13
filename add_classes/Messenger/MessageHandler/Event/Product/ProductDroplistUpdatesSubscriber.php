<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Product;

use App\Common\Contracts\Droplist\ItemStatus;
use App\Common\Database\Model;
use App\Filesystem\ItemPathGenerator;
use App\Messenger\Message\Command\DropList\ReplaceImage;
use App\Messenger\Message\Event\Droplist\DroplistEntryPriceChangedEvent;
use App\Messenger\Message\Event\Product\ProductChangedVisibilityEvent;
use App\Messenger\Message\Event\Product\ProductInStockEvent;
use App\Messenger\Message\Event\Product\ProductOutOfStockEvent;
use App\Messenger\Message\Event\Product\ProductPendingRequestEvent;
use App\Messenger\Message\Event\Product\ProductPriceChangedEvent;
use App\Messenger\Message\Event\Product\ProductWasBlockedEvent;
use App\Messenger\Message\Event\Product\ProductWasDraftEvent;
use App\Messenger\Message\Event\Product\ProductWasModeratedEvent;
use App\Messenger\Message\Event\Product\ProductWasUnblockedEvent;
use DateTimeImmutable;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Update droplist records on product updates.
 */
final class ProductDroplistUpdatesSubscriber implements MessageSubscriberInterface
{
    private MessageBusInterface $eventBus;

    private Model $droplistModel;

    private Model $productModel;

    public function __construct(
        MessageBusInterface $eventBus,
        Model $droplistModel,
        Model $productModel,
        MessageBusInterface $commandBus
    ) {
        $this->eventBus = $eventBus;
        $this->droplistModel = $droplistModel;
        $this->productModel = $productModel;
        $this->commandBus = $commandBus;
    }

    /**
     * Event on product price has been changed.
     */
    public function onProductPriceUpdate(ProductPriceChangedEvent $message): void
    {
        $product = $this->productModel->findOne($message->getProductId());
        if (empty($product)) {
            return;
        }

        foreach ($this->getDroplistRecords($message->getProductId()) as $dropListEntry) {
            if ($dropListEntry['droplist_price']->greaterThan($message->getPrice())) {
                $this->eventBus->dispatch(new DroplistEntryPriceChangedEvent($dropListEntry['id'], $dropListEntry['item_price']), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
        }

        $this->droplistModel->updateMany(
            [
                'item_price'        => $message->getPrice(),
                'price_changed_at'  => DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Event on product has been set to out of stock.
     */
    public function onProductOutOfStock(ProductOutOfStockEvent $message): void
    {
        $this->droplistModel->updateMany(
            [
                'item_status' => ItemStatus::OUT_OF_STOCK(),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Event on product has been set to in stock.
     */
    public function onProductInStock(ProductInStockEvent $message): void
    {
        $product = $this->productModel->findOne(
            $message->getProductId(),
            [
                'with' => [
                    'seller',
                ],
            ]
        );

        if (empty($product)) {
            return;
        }

        $status = ItemStatus::fromItemParameters(
            $product['visible'],
            $product['moderation_is_approved'],
            $product['is_out_of_stock'],
            $product['seller']['status'],
            $product['draft']
        );

        $this->droplistModel->updateMany(
            [
                'item_status' => $status,
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Event on product has been set to blocked.
     */
    public function onProductBlock(ProductWasBlockedEvent $message): void
    {
        $this->droplistModel->updateMany(
            [
                'item_status' => ItemStatus::BLOCKED(),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Event on product has been set to unblocked.
     */
    public function onProductUnblock(ProductWasUnblockedEvent $message): void
    {
        $product = $this->productModel->findOne(
            $message->getProductId(),
            [
                'with' => [
                    'seller',
                ],
            ]
        );

        if (empty($product)) {
            return;
        }

        $status = ItemStatus::fromItemParameters(
            $product['visible'],
            $product['moderation_is_approved'],
            $product['is_out_of_stock'],
            $product['seller']['status'],
            $product['draft']
        );

        $this->droplistModel->updateMany(
            [
                'item_status' => $status,
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Event on product has been set to in moderation.
     */
    public function onProductModerated(ProductWasModeratedEvent $message): void
    {
        $product = $this->productModel->findOne(
            $message->getProductId(),
            [
                'with' => [
                    'seller',
                    'mainPhoto',
                ],
            ]
        );

        if (empty($product)) {
            return;
        }

        $this->droplistModel->updateMany(
            [
                'item_title'    => $product['title'],
                'item_status'   => ItemStatus::fromItemParameters(
                    $product['visible'],
                    $product['moderation_is_approved'],
                    $product['is_out_of_stock'],
                    $product['seller']['status'],
                    $product['draft']
                ),
            ],
            [
                'scopes' => [
                    'itemId' => $product['id'],
                ],
            ]
        );

        $pathToImage = ItemPathGenerator::itemMainPhotoPath($product['id'], $product['main_photo']['photo_name'] ?? 'no-image.jpg');
        foreach ($this->getDroplistRecords($message->getProductId()) as $dropListEntry) {
            $this->commandBus->dispatch(new ReplaceImage($dropListEntry['id'], $pathToImage, 'public.storage'), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
    }

    /**
     * Prduct was marked as draft.
     */
    public function onProductDraft(ProductWasDraftEvent $message)
    {
        $this->droplistModel->updateMany(
            [
                'item_status' => ItemStatus::DRAFT(),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Product need to bee moderated.
     */
    public function onProductPending(ProductPendingRequestEvent $message)
    {
        $this->droplistModel->updateMany(
            [
                'item_status' => ItemStatus::ON_MODERATION(),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * Product visible was changed
     */
    public function onProductChangedVisibility(ProductChangedVisibilityEvent $message)
    {
        $product = $this->productModel->findOne(
            $message->getProductId(),
            [
                'with' => [
                    'seller',
                ],
            ]
        );

        if (empty($product)) {
            return;
        }

        $this->droplistModel->updateMany(
            [
                'item_status' => ItemStatus::fromItemParameters(
                    $message->getVisible(),
                    $product['moderation_is_approved'],
                    $product['is_out_of_stock'],
                    $product['seller']['status'],
                    $product['draft']
                ),
            ],
            [
                'scopes' => [
                    'itemId' => $message->getProductId(),
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ProductPriceChangedEvent::class       => ['bus' => 'event.bus', 'method' => 'onProductPriceUpdate'];
        yield ProductOutOfStockEvent::class         => ['bus' => 'event.bus', 'method' => 'onProductOutOfStock'];
        yield ProductInStockEvent::class            => ['bus' => 'event.bus', 'method' => 'onProductInStock'];
        yield ProductWasBlockedEvent::class         => ['bus' => 'event.bus', 'method' => 'onProductBlock'];
        yield ProductWasUnblockedEvent::class       => ['bus' => 'event.bus', 'method' => 'onProductUnblock'];
        yield ProductWasModeratedEvent::class       => ['bus' => 'event.bus', 'method' => 'onProductModerated'];
        yield ProductWasDraftEvent::class           => ['bus' => 'event.bus', 'method' => 'onProductDraft'];
        yield ProductPendingRequestEvent::class     => ['bus' => 'event.bus', 'method' => 'onProductPending'];
        yield ProductChangedVisibilityEvent::class  => ['bus' => 'event.bus', 'method' => 'onProductChangedVisibility'];
    }

    /**
     * Get items info.
     */
    private function getDroplistRecords(int $productId): \Generator
    {
        $page = 1;
        while (
            !empty(
                $list = $this->droplistModel->findAllBy(
                    [
                        'limit'     => 100,
                        'skip'      => 100 * ($page - 1),
                        'scopes'    => [
                            'itemId' => $productId,
                        ],
                    ]
                )
            )
        ) {
            ++$page;

            yield from $list;
        }
    }
}
