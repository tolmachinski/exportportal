<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Matrix;

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
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Command\Matrix\CreateCargoRoom;
use App\Messenger\Message\Event\Matrix\UserCargoRoomAddedEvent;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\RoomTagPostion;
use ExportPortal\Matrix\Client\Model\StateEvent;
use GuzzleHttp\Exception\RequestException;
use Matrix_Spaces_Model as MatrixSpacesRepository;
use Matrix_Users_Model as MatrixUsersRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Creates the Cargo room for user.
 *
 * @author Anton Zencenco
 */
final class CreateCargoRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
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

    /**
     * Handles the message.
     */
    public function __invoke(CreateCargoRoom $message): void
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
        if (null !== $userReference['cargo_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The the Cargo room for user with ID "%d" already exists in the sync table.', $userId), [
                    'userId'  => $userId,
                    'roomId'  => $userReference['cargo_room_id'],
                    'message' => $message,
                ]);
            }

            return;
        }

        $serviceUser = $this->matrixConnector->getServiceUserAccount();
        $matrixConfigs = $this->matrixConnector->getConfig();

        try {
            $roomReference = $this->roomFactory->create(\translate('matrix_chat_cargo_room_display_name'), $this->prepareRoomOptions(
                $matrixConfigs->getUserNamingStrategy(),
                $serviceUser->getUserId(),
                $userReference['mxid'],
                $matrixConfigs->getHomeserverName()
            ));
            // Add server notices room tag to the room.
            $this->addCargoNoticesTagsToTheRoom(
                $this->matrixConnector->getMatrixClient(),
                $serviceUser,
                $roomReference->getRoomId(),
                $userReference['mxid'],
                $matrixConfigs->getEventNamespace()
            );
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
            new UserCargoRoomAddedEvent((int) $userReference['id_user'], $roomReference->getRoomId()),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateCargoRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Prepares options for room creation.
     */
    protected function prepareRoomOptions(
        UserNamingStrategyInterface $namingStrategy,
        string $serviceUserId,
        string $userMxId,
        string $homeserver
    ): RoomMessageOptions {
        $options = (new RoomMessageOptions())
            ->alias($namingStrategy->cargoNoticesRoomNameForMatrixId($userMxId))
            ->direct(true)
            ->topic(\translate('matrix_chat_cargo_room_topic'))
            ->invites([$userMxId])
            ->senderId($serviceUserId)
            ->encrypted(false)
            ->visibility((string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->initialState($this->getCargoNoticesStates())
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
     *
     * @return StateEvent[]
     */
    protected function getCargoNoticesStates(): array
    {
        $states = [
            (new StateEvent())->setType((string) StateEventType::JOIN_RULE())->setContent(['join_rule' => (string) JoinRule::INVITE()]),
            (new StateEvent())->setType((string) StateEventType::GUEST_ACCESS())->setContent(['guest_access' => (string) GuestAccess::FORBIDDEN()]),
            (new StateEvent())->setType((string) StateEventType::HISTORY_VISIBILITY())->setContent(['history_visibility' => (string) HistoryVisibility::INVITED()]),
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
        $states[] = (new StateEvent())->setType((string) StateEventType::AVATAR())->setContent(['url' => $imageUrl]);

        return $states;
    }

    /**
     * Tags the room to indicate that it is server notice room.
     */
    protected function addCargoNoticesTagsToTheRoom(
        MatrixClient $matrixClient,
        AuthenticatedUser $adminUser,
        string $roomId,
        string $userMxId,
        string $eventNamespace
    ): void {
        $tags = ['m.server_notice', "{$eventNamespace}.cargo"];
        $userDataApi = $matrixClient->getUserDataApi();
        try {
            $user = $this->matrixConnector->loginAsMatrixUser($adminUser, $userMxId, 300000);
            $userDataApi->getConfig()->setAccessToken($user->getAccessToken());
            foreach ($tags as $position => $tag) {
                try {
                    $userDataApi->setRoomTag((new RoomTagPostion())->setOrder($position), $user->getUserId(), $roomId, $tag);
                } catch (ApiException $e) {
                    throw new ContextAwareException(
                        sprintf('Failed to add the tag "%s" to the room with name "%s" on this matrix server.', $tag, $roomId),
                        ['userId' => $user->getUserId(), 'roomId' => $roomId],
                        $e->getCode(),
                        $e
                    );
                }
            }
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to add tags to the room with name "%s" on this matrix server', $roomId),
                ['userId' => $user->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        } finally {
            // After that we need to logout current user.
            try {
                if ($user) {
                    $this->matrixConnector->logoutUser($user);
                }
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
        $this->matrisUsersRepository->getConnection()->transactional(function () use ($userReferenceId, $roomId) {
            $this->matrisUsersRepository->updateOne($userReferenceId, [
                'cargo_room_id'        => $roomId,
                'create_cargo_room_at' => new \DateTimeImmutable(),
            ]);
        });
    }
}
