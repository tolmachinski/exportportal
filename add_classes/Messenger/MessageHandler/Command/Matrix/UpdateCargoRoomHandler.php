<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Matrix;

use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\StateEventType;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Command\Matrix\UpdateCargoRoom;
use App\Messenger\Message\Event\Matrix\UserCargoRoomUpdatedEvent;
use ExportPortal\Matrix\Client\Api\RoomParticipationApi;
use ExportPortal\Matrix\Client\Model\StateEvent;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Creates the Cargo room for user.
 *
 * @author Anton Zencenco
 */
final class UpdateCargoRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The event bus.
     */
    private MessageBusInterface $eventBus;

    public function __construct(MessageBusInterface $eventBus, MatrixConnector $matrixConnector)
    {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->eventBus = $eventBus;
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * Handles the message.
     */
    public function __invoke(UpdateCargoRoom $message): void
    {
        // Retrieve the sync reference.
        if (null === $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId = $message->getUserId())) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user with ID "%d" is not present in the sync table.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null === $userReference['user'] ?? null) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user with ID "%d" is not affiliated to the user account.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null === $userReference['cargo_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The the Cargo room for user with ID "%d" is not yet created in the sync table.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }

        try {
            $serviceUser = $this->matrixConnector->getServiceUserAccount();
            $states = $this->getRoomStates();
            /** @var RoomParticipationApi $roomApi */
            $roomApi = \tap(
                $this->matrixConnector->getMatrixClient()->getRoomParticipationApi(),
                fn (RoomParticipationApi $api) => $api->getConfig()->setAccessToken($serviceUser->getAccessToken())
            );

            foreach ($states as $state) {
                $roomApi->setRoomStateWithKey($userReference['cargo_room_id'], $state->getType(), '', $state->getContent());
            }
        } catch (ContextAwareException | RequestException | \Throwable $e) {
            // Write exeption to the logs
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');

            // Forward exception and prevent retry.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }

        // Send event that server room was created
        $this->eventBus->dispatch(new Envelope(
            new UserCargoRoomUpdatedEvent((int) $userReference['id_user'], $userReference['cargo_room_id']),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield UpdateCargoRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Get server notices room stats.
     *
     * @return StateEvent[]
     */
    protected function getRoomStates(): array
    {
        $states = [
            (new StateEvent())->setType((string) StateEventType::NAME())->setContent((object) ['name' => \translate('matrix_chat_cargo_room_display_name')]),
            (new StateEvent())->setType((string) StateEventType::TOPIC())->setContent((object) ['topic' => \translate('matrix_chat_cargo_room_topic')]),
        ];

        if (null !== ($avatarPath = \config('matrix_cargo_room_avatar_path') ?? null)) {
            $imageUrl = \asset($avatarPath);
            if (
                !\filter_var($imageUrl, \FILTER_VALIDATE_URL)
                && \file_exists(
                    \sprintf('%s/%s', \rtrim(\App\Common\ROOT_PATH, '\\/'), \ltrim($imageUrl, '/'))
                )
            ) {
                $imageUrl = \getUrlForGroup(\ltrim($imageUrl, '\\/'));
            }
        }
        $states[] = (new StateEvent())->setType((string) StateEventType::AVATAR())->setContent((object) ['url' => $imageUrl]);

        return $states;
    }
}
