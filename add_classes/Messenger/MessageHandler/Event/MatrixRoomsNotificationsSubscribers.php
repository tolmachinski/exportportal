<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Messenger\Message\Event\Matrix\UserCargoRoomAddedEvent;
use App\Messenger\Message\Event\MatrixChatRoomAddedEvent;
use App\Messenger\Message\Event\MatrixUserKeysCreatedEvent;
use App\Messenger\Message\Event\MatrixUserServerNoticesRoomAddedEvent;
use App\Services\ChatBindingService;
use ExportPortal\Bridge\Matrix\Notifier\Notification\MatrixNotification;
use ExportPortal\Component\Notifier\Bridge\Matrix\MatrixOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * Sends the notification when something happens with the matrix rooms.
 *
 * @author Anton Zencenco
 */
final class MatrixRoomsNotificationsSubscribers implements MessageSubscriberInterface
{
    /**
     * The matriux connector instance.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The chat binding service.
     */
    private ChatBindingService $bindingService;

    /**
     * The notifier service.
     */
    private NotifierInterface $notifier;

    /**
     * The model locator.
     */
    private ModelLocator $modelLocator;

    /**
     * The logger instance.
     */
    private LoggerInterface $logger;

    /**
     * @param MatrixConnector    $matrixConnector the matrix connector
     * @param ChatBindingService $bindingService  the chat bindings service
     * @param NotifierInterface  $notifier        the notifier service
     * @param ModelLocator       $modelLocator    the model locator
     * @param LoggerInterface    $logger          the logger
     */
    public function __construct(
        MatrixConnector $matrixConnector,
        ChatBindingService $bindingService,
        NotifierInterface $notifier,
        ModelLocator $modelLocator,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? $matrixConnector->getConfig()->getLogger();
        $this->notifier = $notifier;
        $this->modelLocator = $modelLocator;
        $this->bindingService = $bindingService;
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * Send notification when chat room is created.
     */
    public function onChatRoomCreated(MatrixChatRoomAddedEvent $message)
    {
        // Leave if there is no room options or there is not resources.
        if (
            null === ($roomOptions = $message->getOptions())
            || null === ($resourceOptions = $roomOptions->getResource())
            || null === $resourceOptions->getType()
        ) {
            return;
        }

        // Get the resource attributes and look for the notification subject and content.
        $resourceAttributes = $resourceOptions->getAttributes();
        if (empty($resourceAttributes)) {
            // If the attributes are empty, then we cannot send anything.
            return;
        }

        //region Legacy notification
        // TODO: change when legacy notifier will be moved to notifier
        $messageCode = $resourceAttributes['create.message_code'] ?? null;
        $messageRecipients = $resourceAttributes['create.recipients'] ?? [];
        $messageReplacements = $resourceAttributes['create.message_replacements'] ?? [];
        // If message code is not empty, we will send notification in legacy format first
        if (null !== $messageCode) {
            // If there is no recipietns in the attributes, then we will extract them from the
            // chat room bindings parameters.
            if (empty($messageRecipients)) {
                list('senderId' => $senderId, 'recipentId' => $recipientId) = $this->bindingService->getBindingParamters(
                    $resourceOptions,
                    $message->getRoomId(),
                    $roomOptions->getSenderId(),
                    $roomOptions->getRecipientId()
                );
                $messageRecipients = \array_filter([$senderId, $recipientId]);
            }
            /** @var \Notify_Model $legacyNotificationModel */
            $legacyNotificationModel = $this->modelLocator->get(\Notify_Model::class);
            $legacyNotificationModel->send_notify([
                'systmess'  => true,
                'mess_code' => $messageCode,
                'id_users'  => $messageRecipients,
                'replace'   => $messageReplacements,
            ]);
        }
        //endregion Legacy notification

        //region Notification
        $subject = $resourceAttributes['create.notification_subject'] ?? null;
        $content = $resourceAttributes['create.notification_content'] ?? null;
        // If at least notification subject is not empty, then we can send the notification
        if (null !== $subject) {
            $this->notifier->send(new MatrixNotification($message->getRoomId(), $subject, $content));
        }
        //endregion Notification
    }

    /**
     * Send welcoming notification into the server notices room when user's were create.
     */
    public function onKeysCreatedSendWelcomimgMessage(MatrixUserKeysCreatedEvent $message): void
    {
        if (null === ($userReference = $this->getUserMatrixReference($userId = $message->getUserId()))) {
            return;
        }

        $this->sendServiceRoomNotification($userId, $userReference['server_notices_room_id'] ?? null);
        $this->sendCargoRoomNotification($userId, $userReference['cargo_room_id'] ?? null);
    }

    /**
     * Send welcoming notification into the server notices room it was created.
     */
    public function onServerNoticesRoomCreated(MatrixUserServerNoticesRoomAddedEvent $message): void
    {
        if (
            null === ($userReference = $this->getUserMatrixReference($userId = $message->getUserId()))
            // We cannot send the message into the room if keys are not created.
            || !$userReference['has_initialized_keys']
        ) {
            return;
        }

        $this->sendServiceRoomNotification($userId, $userReference['server_notices_room_id'] ?? null);
    }

    /**
     * Send welcoming notification into the server notices room it was created.
     */
    public function onCargoRoomCreated(UserCargoRoomAddedEvent $message): void
    {
        if (
            null === ($userReference = $this->getUserMatrixReference($userId = $message->getUserId()))
            // We cannot send the message into the room if keys are not created.
            || !$userReference['has_initialized_keys']
        ) {
            return;
        }

        $this->sendCargoRoomNotification($userId, $userReference['cargo_room_id'] ?? null);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield UserCargoRoomAddedEvent::class => ['bus' => 'event.bus', 'method' => 'onCargoRoomCreated'];
        yield MatrixChatRoomAddedEvent::class => ['bus' => 'event.bus', 'method' => 'onChatRoomCreated'];
        yield MatrixUserKeysCreatedEvent::class => ['bus' => 'event.bus', 'method' => 'onKeysCreatedSendWelcomimgMessage'];
        yield MatrixUserServerNoticesRoomAddedEvent::class => ['bus' => 'event.bus', 'method' => 'onServerNoticesRoomCreated'];
    }

    /**
     * Returns the matrix reference for user ID.
     */
    private function getUserMatrixReference(int $userId): ?array
    {
        if (null === ($userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId))) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The sync reference for user with ID "%s" is not present in the sync table.', $userId), [
                    'userId'  => $userId,
                ]);
            }
        }

        return $userReference;
    }

    /**
     * Sends first notification to the service room when the keys are created.
     */
    private function sendServiceRoomNotification(int $userId, ?string $roomtId): void
    {
        if (null === $roomtId) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The server notices room is not yet created for user with ID "%s"', $userId), [
                    'userId'  => $userId,
                ]);
            }

            return;
        }

        $this->notifier->send(
            new MatrixNotification(
                $roomtId,
                \translate('matrix_chat_server_notices_room_creation_subject'),
                \translate('matrix_chat_server_notices_room_creation_content')
            )
        );
    }

    /**
     * Sends first notification to the cargo when the keys are created.
     */
    private function sendCargoRoomNotification(int $userId, ?string $roomtId): void
    {
        if (null === $roomtId) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The cargo room is not yet created for user with ID "%s"', $userId), [
                    'userId'  => $userId,
                ]);
            }

            return;
        }

        $this->notifier->send(
            (new MatrixNotification(
                $roomtId,
                \translate('matrix_chat_cargo_room_creation_subject'),
                \translate('matrix_chat_cargo_room_creation_content')
            ))
                ->messageType(MatrixOptions::MESSAGE_TYPE_SYSTEM_NOTICE)
                ->importance(MatrixNotification::IMPORTANCE_LOW)
        );
    }
}
