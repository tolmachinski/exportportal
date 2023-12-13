<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Company;

use App\Messenger\Message\Event\Company as CompanyEvents;
use App\Services\EditRequest\CompanyEditRequestDocumentsService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Deletes the documents when company edit request is declined.
 *
 * @author Anton Zencenco
 */
final class DeleteDocumentsWhenRequestDeclined implements MessageSubscriberInterface
{
    /**
     * The company edit requests documents service.
     */
    private CompanyEditRequestDocumentsService $documentsService;

    /**
     * @param CompanyEditRequestDocumentsService $documentsService the company edit requests documents service
     */
    public function __construct(CompanyEditRequestDocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    /**
     * Handles the event when company edit request is declined.
     */
    public function onDeclineRequest(CompanyEvents\DeclinedEditRequestEvent $message): void
    {
        $this->documentsService->deleteDocuments($message->getRequestId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CompanyEvents\DeclinedEditRequestEvent::class => ['bus' => 'event.bus', 'method' => 'onDeclineRequest'];
    }
}
