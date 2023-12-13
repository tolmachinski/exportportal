<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Profile;

use App\Messenger\Message\Event\Profile as ProfileEvents;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Deletes the documents when profile edit request is declined.
 *
 * @author Anton Zencenco
 */
final class DeleteDocumentsWhenRequestDeclined implements MessageSubscriberInterface
{
    /**
     * The profile edit requests documents service.
     */
    private ProfileEditRequestDocumentsService $documentsService;

    /**
     * @param ProfileEditRequestDocumentsService $documentsService the profile edit requests documents service
     */
    public function __construct(ProfileEditRequestDocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    /**
     * Handles the event when profile edit request is declined.
     */
    public function onDeclineRequest(ProfileEvents\DeclinedEditRequestEvent $message): void
    {
        $this->documentsService->deleteDocuments($message->getRequestId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ProfileEvents\DeclinedEditRequestEvent::class => ['bus' => 'event.bus', 'method' => 'onDeclineRequest'];
    }
}
