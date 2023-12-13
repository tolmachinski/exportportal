<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Media;

use App\Messenger\Message\Command\Media\CopyFileToStorage;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Processes the image using.
 */
final class CopyFileToStorageHandler implements MessageSubscriberInterface
{
    /**
     * The instance of the image handler (legacy variant).
     */
    private FilesystemProviderInterface $storageProvider;

    /**
     * Undocumented function.
     */
    public function __construct(FilesystemProviderInterface $storageProvider)
    {
        $this->storageProvider = $storageProvider;
    }

    /**
     * Handles the image processing message.
     */
    public function __invoke(CopyFileToStorage $message): void
    {
        if (empty($message->getFilePath()) || empty($message->getDestination())) {
            return;
        }

        $storage = $this->storageProvider->storage($message->getStorage());
        $storage->copy(
            $message->getFilePath(),
            $message->getDestination()
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CopyFileToStorage::class => ['bus' => 'event.bus'];
    }
}
