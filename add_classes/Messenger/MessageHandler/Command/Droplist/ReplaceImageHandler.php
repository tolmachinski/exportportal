<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Droplist;

use App\Filesystem\ItemDroplistFilePathGenerator;
use App\Messenger\Message\Command\DropList\ReplaceImage;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Items_Droplist_Model;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Processes the image using.
 */
final class ReplaceImageHandler implements MessageSubscriberInterface
{
    /**
     * The instance of the image handler (legacy variant).
     */
    private FilesystemProviderInterface $storageProvider;

    /**
     * Items_Droplist_Model.
     */
    private Items_Droplist_Model $droplistModel;

    /**
     * Items_Droplist_Model.
     */
    private FilesystemOperator $droplistStorage;

    /**
     * Undocumented function.
     */
    public function __construct(FilesystemProviderInterface $storageProvider, Items_Droplist_Model $droplistModel)
    {
        $this->storageProvider = $storageProvider;
        $this->droplistModel = $droplistModel;
        $this->droplistStorage = $storageProvider->storage('public.storage');
    }

    /**
     * Handles the image processing message.
     */
    public function __invoke(ReplaceImage $message): void
    {
        $droplistItem = $this->droplistModel->findOne($message->getId());
        if (empty($droplistItem)) {
            return;
        }

        $this->droplistModel->updateOne($droplistItem['id'], [
            'item_image' => $fileName = sprintf(
                '%s.%s',
                \bin2hex(\random_bytes(16)),
                \pathinfo($message->getSourceImagePath(), PATHINFO_EXTENSION)
            ),
        ]);

        $storage = $this->storageProvider->storage($message->getSourceStorage());

        $this->droplistStorage->delete(
            ItemDroplistFilePathGenerator::droplistImagePath($droplistItem['id'], $droplistItem['item_image'])
        );

        $this->droplistStorage->write(
            ItemDroplistFilePathGenerator::droplistImagePath($droplistItem['id'], $fileName),
            $storage->read($message->getSourceImagePath())
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ReplaceImage::class => ['bus' => 'command.bus'];
    }
}
