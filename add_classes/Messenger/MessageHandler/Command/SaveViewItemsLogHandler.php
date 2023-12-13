<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Common\Database\Model;
use App\Messenger\Message\Command\SaveViewItemsLog;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Save industry handler.
 *
 * @author Bendiucov Tatiana
 */
class SaveViewItemsLogHandler implements MessageSubscriberInterface
{
    private Model $itemsViewsModel;

    public function __construct(Model $itemsViewsModel)
    {
        $this->itemsViewsModel = $itemsViewsModel;
    }

    public function __invoke(SaveViewItemsLog $message)
    {
        $this->itemsViewsModel->insertOne([
            'item_id' => $message->getItemId(),
            'user_id' => $message->getUserId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield SaveViewItemsLog::class => ['bus' => 'command.bus'];
    }
}
