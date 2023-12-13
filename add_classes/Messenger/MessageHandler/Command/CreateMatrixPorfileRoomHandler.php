<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\GuestAccess;
use App\Bridge\Matrix\HistoryVisibility;
use App\Bridge\Matrix\JoinRule;
use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\Room\RoomFactoryInterface as MatrixRoomFactoryInterface;
use App\Bridge\Matrix\RoomVisibility;
use App\Bridge\Matrix\StateEventType;
use App\Common\Contracts\Group\GroupType;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotSupportedException;
use App\Messenger\Message\Command\CreateMatrixPorfileRoom;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Event\MatrixUserProfileRoomAddedEvent;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Model\RoomTagPostion;
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
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Creates the user's profile room after the user's account was created on matrix server.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixPorfileRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
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

    public function __invoke(CreateMatrixPorfileRoom $message): void
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
        if (null === $userData = $userReference['user'] ?? null) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user with ID "%d" is not affiliated to the user account.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null !== $userReference['profile_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The room for sync reference user with ID "%d" is already exists int the sync table.', $userId), [
                    'userId'  => $userId,
                    'roomId'  => $userReference['profile_room_id'],
                    'message' => $message,
                ]);
            }

            return;
        }

        $matrixConfigs = $this->matrixConnector->getConfig();
        $namingStrategy = $matrixConfigs->getUserNamingStrategy();
        $serviceUser = $this->matrixConnector->getServiceUserAccount();
        $options = (new RoomMessageOptions())
            ->alias($namingStrategy->profileRoomNameForUserId((string) $userId))
            ->senderId($userReference['mxid'])
            ->encrypted(false)
            ->visibility((string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->initialState($this->getProfileStates($userData, $eventNamespace = $matrixConfigs->getEventNamespace()))
            ->inviteServiceUsers(false)
            ->powerLevels([
                'users'          => [$userReference['mxid'] => 50],
                'notifications'  => ['room' => 50],
                'users_default'  => 0,
                'events_default' => 100, // Prevent from sending messages
                'state_default'  => 50,
                'invite'         => 50,
                'redact'         => 100,
                'kick'           => 100,
                'ban'            => 100,
            ])
        ;
        // Get space
        $profileSpace = $this->spacesRepository->findByName('profiles');
        if (null !== $profileSpace) {
            $options->addParent($profileSpace['room_id'] ?? null, true, [$matrixConfigs->getHomeserverName()]);
        }

        try {
            $roomReference = $this->roomFactory->create($userData['full_name'], $options);
            // Add profile tag to the room.
            $this->addProfileTagToTheRoom($this->matrixConnector->getMatrixClient(), $serviceUser, $eventNamespace, $roomReference->getRoomId(), $userReference['mxid']);
            // Write room information to the database.
            $this->writeRoom($userReference['id'], $roomReference->getRoomId(), $namingStrategy->profileRoomAliasForUserId((string) $userId));
        } catch (NotSupportedException $e) {
            if ($this->logger) {
                $this->logger->alert('Cannot create room with missing name and options');
            }

            return;
        } catch (ContextAwareException | RequestException | \Throwable $e) {
            // Write exeption to the logs.
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            // And delete room if it was created.
            if (isset($roomReference)) {
                $this->commandBus->dispatch(new DeleteMatrixRoom($roomReference->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
            }

            // Forward exception and prevent retry.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }

        // Notify the application about created profile room.
        $this->eventBus->dispatch(new Envelope(
            new MatrixUserProfileRoomAddedEvent((int) $userReference['id_user'], $roomReference->getRoomId()),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateMatrixPorfileRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Get profile room stats.
     */
    protected function getProfileStates(array $userData, string $eventNamspace): array
    {
        $roomStates = [
            (new StateEvent())
                ->setType((string) StateEventType::from(StateEventType::JOIN_RULE))
                ->setContent([
                    'join_rule' => (string) JoinRule::from(JoinRule::PUBLIC),
                ]),
            (new StateEvent())
                ->setType((string) StateEventType::from(StateEventType::GUEST_ACCESS))
                ->setContent([
                    'guest_access' => (string) GuestAccess::from(GuestAccess::FORBIDDEN),
                ]),
            (new StateEvent())
                ->setType((string) StateEventType::from(StateEventType::HISTORY_VISIBILITY))
                ->setContent([
                    'history_visibility' => (string) HistoryVisibility::from(HistoryVisibility::WORLD_READABLE),
                ]),
            (new StateEvent())->setType("{$eventNamspace}.profile_room")->setContent(['type' => 'room']),
        ];
        if (GroupType::from(GroupType::ADMIN) === $userData['group_type']) {
            $roomStates[] = (new StateEvent())->setType("{$eventNamspace}.administration")->setContent(['type' => (string) $userData['group_alias']]);
        }
        if (GroupType::from(GroupType::EP_STAFF) === $userData['group_type']) {
            $roomStates[] = (new StateEvent())->setType("{$eventNamspace}.staff")->setContent(['type' => (string) $userData['group_alias']]);
        }

        return $roomStates;
    }

    /**
     * Tags the room to indicate that it is the profile room.
     */
    protected function addProfileTagToTheRoom(MatrixClient $matrixClient, AuthenticatedUser $adminUser, string $tagNamespace, string $roomId, string $userMxId): void
    {
        try {
            $user = $this->matrixConnector->loginAsMatrixUser($adminUser, $userMxId, 300000);
            $userDataApi = $matrixClient->getUserDataApi();
            $userDataApi->getConfig()->setAccessToken($user->getAccessToken());
            $userDataApi->setRoomTag((new RoomTagPostion())->setOrder(0), $user->getUserId(), $roomId, $tagName = "{$tagNamespace}.profile");
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
    protected function writeRoom(int $userReferenceId, string $roomId, string $roomAlias): void
    {
        $connection = $this->matrisUsersRepository->getConnection();
        $connection->beginTransaction();

        try {
            $this->matrisUsersRepository->updateOne($userReferenceId, [
                'profile_room_id'     => $roomId,
                'profile_room_alias'  => $roomAlias,
                'create_room_at_date' => new \DateTimeImmutable(),
            ]);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }
}
