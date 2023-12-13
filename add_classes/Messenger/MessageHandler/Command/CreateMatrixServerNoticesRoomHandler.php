<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\GuestAccess;
use App\Bridge\Matrix\HistoryVisibility;
use App\Bridge\Matrix\JoinRule;
use App\Bridge\Matrix\Mapping\UserNamingStrategyInterface;
use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\Room\RoomFactoryInterface as MatrixRoomFactoryInterface;
use App\Bridge\Matrix\RoomVisibility;
use App\Bridge\Matrix\StateEventType;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotSupportedException;
use App\Messenger\Message\Command\CreateMatrixServerNoticesRoom;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Event\MatrixUserServerNoticesRoomAddedEvent;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Model\StateEvent;
use GuzzleHttp\Exception\RequestException;
use Matrix_Spaces_Model as MatrixSpacesRepository;
use Matrix_Users_Model as MatrixUsersRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\RoomTagPostion;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Creates the server notices room for user.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixServerNoticesRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The rooms factory.
     */
    protected MatrixRoomFactoryInterface $roomFactory;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * The event bus.
     */
    private MessageBusInterface $eventBus;

    /**
     * The matrix user reference repository.
     */
    private MatrixUsersRepository $matrisUsersRepository;

    /**
     * The rooms local repository.
     */
    private MatrixSpacesRepository $spacesRepository;

    /**
     * @param MessageBusInterface $commandBus the command bus
     */
    public function __construct(
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus,
        MatrixConnector $matrixConnector,
        MatrixRoomFactoryInterface $roomFactory,
        MatrixUsersRepository $matrisUsersRepository,
        MatrixSpacesRepository $spacesRepository
    ) {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->roomFactory = $roomFactory;
        $this->matrixConnector = $matrixConnector;
        $this->spacesRepository = $spacesRepository;
        $this->matrisUsersRepository = $matrisUsersRepository;
    }

    public function __invoke(CreateMatrixServerNoticesRoom $message): void
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
        if (null !== $userReference['server_notices_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The the server notices room for user with ID "%d" already exists int the sync table.', $userId), [
                    'userId'  => $userId,
                    'roomId'  => $userReference['server_notices_room_id'],
                    'message' => $message,
                ]);
            }

            return;
        }

        $serviceUser = $this->matrixConnector->getServiceUserAccount();
        $matrixConfigs = $this->matrixConnector->getConfig();

        try {
            $roomReference = $this->roomFactory->create(\translate('matrix_chat_server_notices_room_display_name'), $this->prepareRoomOptions(
                $matrixConfigs->getUserNamingStrategy(),
                $serviceUser->getUserId(),
                $userReference['mxid'],
                $matrixConfigs->getHomeserverName()
            ));
            // Add server notices room tag to the room.
            $this->addServerNoticesTagToTheRoom($this->matrixConnector->getMatrixClient(), $serviceUser, $roomReference->getRoomId(), $userReference['mxid']);
            // Write room information to the database.
            $this->writeRoom($userReference['id'], $roomReference->getRoomId());
        } catch (NotSupportedException $e) {
            if ($this->logger) {
                $this->logger->alert('Cannot create room with missing name and options.');
            }

            throw $e;
        } catch (ContextAwareException | RequestException | \Throwable $e) {
            // Write exeption to the logs
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            // And delete room if it was created
            if (isset($roomReference)) {
                $this->commandBus->dispatch(new DeleteMatrixRoom($roomReference->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
            }

            // Forward exception and prevent retry.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }

        // Send event that server room was created
        $this->eventBus->dispatch(new Envelope(
            new MatrixUserServerNoticesRoomAddedEvent((int) $userReference['id_user'], $roomReference->getRoomId()),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateMatrixServerNoticesRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Prepares options for room creation.
     */
    protected function prepareRoomOptions(UserNamingStrategyInterface $namingStrategy, string $serviceUserId, string $userMxId, string $homeserver): RoomMessageOptions
    {
        $options = (new RoomMessageOptions())
            ->alias($namingStrategy->serverNoticesRoomNameForMatrixId($userMxId))
            ->direct(true)
            ->topic(\translate('matrix_chat_server_notices_room_topic'))
            ->invites([$userMxId])
            ->senderId($serviceUserId)
            ->encrypted(false)
            ->visibility((string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->initialState($this->getServerNoticesStates(\config('matrix_server_notices_room_avatar_path', null)))
            ->inviteServiceUsers(false)
            ->powerLevels([
                'users'          => [$serviceUserId => 100],
                'notifications'  => ['room' => 100],
                'users_default'  => -10,
                'events_default' => 100,
                'state_default'  => 100,
                'invite'         => 100,
                'redact'         => 100,
                'kick'           => 100,
                'ban'            => 100,
            ])
        ;
        // Get space
        $noticesSpace = $this->spacesRepository->findByName('notices');
        if (null !== $noticesSpace) {
            $options->addParent($noticesSpace['room_id'] ?? null, true, [$homeserver]);
        }

        return $options;
    }

    /**
     * Get server notices room stats.
     */
    protected function getServerNoticesStates(?string $avatarPath): array
    {
        $states = [
            (new StateEvent())->setType((string) StateEventType::JOIN_RULE())->setContent(['join_rule' => (string) JoinRule::INVITE()]),
            (new StateEvent())->setType((string) StateEventType::GUEST_ACCESS())->setContent(['guest_access' => (string) GuestAccess::FORBIDDEN()]),
            (new StateEvent())->setType((string) StateEventType::HISTORY_VISIBILITY())->setContent(['history_visibility' => (string) HistoryVisibility::INVITED()]),
        ];

        if (null !== $avatarPath && \file_exists(\rtrim(\App\Common\ROOT_PATH, '/') . $avatarPath)) {
            $states[] = (new StateEvent())->setType((string) StateEventType::AVATAR())->setContent([
                'url' => \asset($avatarPath, 'legacy'),
            ]);
        }

        return $states;
    }

    /**
     * Tags the room to indicate that it is server notice room.
     */
    protected function addServerNoticesTagToTheRoom(MatrixClient $matrixClient, AuthenticatedUser $adminUser, string $roomId, string $userMxId): void
    {
        try {
            $user = $this->matrixConnector->loginAsMatrixUser($adminUser, $userMxId, 300000);
            $userDataApi = $matrixClient->getUserDataApi();
            $userDataApi->getConfig()->setAccessToken($user->getAccessToken());
            $userDataApi->setRoomTag((new RoomTagPostion())->setOrder(0), $user->getUserId(), $roomId, $tagName = 'm.server_notice');
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to add tag "%s" to the room with name "%s" on this matrix server.', $tagName, $roomId),
                ['userId' => $user->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        } finally {
            // After that we need to logout current user.
            try {
                $this->matrixConnector->logoutUser($user);
            } catch (\Throwable $e) {
                // Skip
            }
        }
    }

    /**
     * Writes the room di in the databse.
     */
    protected function writeRoom(int $userReferenceId, string $roomId): void
    {
        $connection = $this->matrisUsersRepository->getConnection();
        $connection->beginTransaction();

        try {
            $this->matrisUsersRepository->updateOne($userReferenceId, ['server_notices_room_id' => $roomId]);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }
}
