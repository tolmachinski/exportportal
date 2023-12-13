<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\NotFoundException;
use ExportPortal\Bridge\Notifier\DataProvider\MessageMetadataProviderInterface;

/**
 * The notification metadata provider service.
 *
 * @author Anton Zencenco
 */
final class NotificationMetadataProvider implements MessageMetadataProviderInterface
{
    /**
     * The notification messa repository.
     */
    private Model $notificationsRepository;

    /**
     * Create the notification metadata provider service class.
     *
     * @param ModelLocator $modelLocator the models locator
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->notificationsRepository = $modelLocator->get(\System_Messages_Model::class);
    }

    /**
     * Get the message metadata by provided message key.
     *
     * @return array{id: int, title: string, text: string, type: string, module: string, category: string}
     */
    public function getMessageMetadata(string $key): ?array
    {
        $metadata = $this->notificationsRepository->findOneBy(['scopes' => ['code' => $key]]);
        if (null === $metadata) {
            return null;
        }

        return [
            'id'       => (int) $metadata['idmess'],
            'type'     => ((string) $metadata['type'] ?? null) ?: null,
            'text'     => $metadata['message'],
            'title'    => $metadata['title'],
            'module'   => (string) $metadata['module'],
            'category' => ((string) $metadata['mess_type'] ?? null) ?: null,
        ];
    }
}
