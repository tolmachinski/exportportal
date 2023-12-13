<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Room;

use App\Bridge\Matrix\Configuration;
use App\Bridge\Matrix\EncryptionAlgorithm;
use App\Bridge\Matrix\Mapping\UserNamingStrategyInterface;
use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\StateEventType;
use App\Common\Database\Model;
use App\Common\Exceptions\ContextAwareException;
use ExportPortal\Matrix\Client\Api\RoomCreationApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\CreateRoomRequest as MatrixRoom;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\RoomReference;
use ExportPortal\Matrix\Client\Model\StateEvent;

abstract class AbstractRoomFactory implements RoomFactoryInterface
{
    /**
     * The matrix client.
     */
    protected MatrixClient $matrixClient;

    /**
     * The matrix configurations.
     */
    protected Configuration $configuration;

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    /**
     * The matrix naming strategy.
     */
    protected UserNamingStrategyInterface $namingStrategy;

    /**
     * The matrix service user.
     */
    protected AuthenticatedUser $serviceUser;

    /**
     * The rooms local repository.
     */
    protected Model $roomsRepository;

    /**
     * The spaces local repository.
     */
    protected Model $spacesRepository;

    /**
     * The flag that indicates if invited users profile room must be attached to the state events.
     */
    protected bool $attachInvitedUsersProfile = true;

    /**
     * @param MatrixConnector $matrixConnector  the matrix connector
     * @param Model           $roomsRepository  the rooms local repository
     * @param Model           $spacesRepository the spaces local repository
     */
    public function __construct(MatrixConnector $matrixConnector, Model $roomsRepository, Model $spacesRepository)
    {
        $this->serviceUser = $matrixConnector->getServiceUserAccount();
        $this->matrixClient = $matrixConnector->getMatrixClient();
        $this->configuration = $matrixConnector->getConfig();
        $this->namingStrategy = $matrixConnector->getConfig()->getUserNamingStrategy();
        $this->matrixConnector = $matrixConnector;
        $this->roomsRepository = $roomsRepository;
        $this->spacesRepository = $spacesRepository;
    }

    /**
     * Creates matrix room.
     */
    protected function doCreateRoom(MatrixClient $matrixClient, AuthenticatedUser $user, MatrixRoom $room): RoomReference
    {
        /** @var RoomCreationApi $roomApi */
        $roomApi = \tap($matrixClient->getRoomCreationApi(), function (RoomCreationApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });

        try {
            return $roomApi->createRoom($room);
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to create new room with name "%s" on this matrix server due to error: %s.', $room->getName(), $e->getMessage()),
                ['userId' => $user->getUserId(), 'room' => $room],
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process room options into accepted form. Returns new instance of options.
     */
    protected function processRoomOptions(
        RoomMessageOptions $options,
        UserNamingStrategyInterface $namingStrategy,
        AuthenticatedUser $serviceUser,
        string $homeserver,
        string $eventNamspace
    ): RoomMessageOptions {
        $serviceUserId = $senderId = $serviceUser->getUserId();

        //region Normalize sender
        if (null !== $options->getSenderId()) {
            // If sender is not NULL and not service user
            if ($options->getSenderId() !== $serviceUserId) {
                $options->senderId(
                    // Process sender ID value to ensure that it is proper mxID value
                    $senderId = $this->normalizeUserMxid($namingStrategy, $options->getSenderId())
                );
            }
        } else {
            // If sender is NULL, then the serveice user will be the sender
            $options->senderId($serviceUserId);
        }
        //endregion Normalize sender

        //region Normalize recipient
        $options->recipientId(
            // Process recipient ID value to ensure that it is proper mxID value
            $recipientId = $this->normalizeUserMxid($namingStrategy, $options->getRecipientId())
        );
        //endregion Normalize recipient

        //region Normalize invite users
        $options->invites(
            // Here we process all ID in the invites list to ensure
            // that all of them are proper mxID values
            $this->preapreRoomInvites($namingStrategy, $options->getInvites() ?? [], $serviceUserId, $senderId, $recipientId, $options->getInviteServiceUsers() ?? true)
        );
        //endregion Normalize invite users

        //region Normalize users power levels
        // First, let's process room power levels.
        $powerLevels = $this->prepareRoomPowerLevels($options, $namingStrategy, $serviceUserId, $senderId, $recipientId);
        // And here we do some nasty shenanigans with the power levels
        // First of all, the value for power levels will be unset
        // Next we will create the power level state event that will be put to the end of the
        // event states stack.
        // This will unsure that the sender's power level will will be modified only AFTER room is created.
        // Otherwise we will fail to create the room.
        $options->powerLevels(null);
        $options->addInitialState(
            (new StateEvent())->setType((string) StateEventType::from(StateEventType::POWER_LEVELS))->setContent($powerLevels)
        );
        //endregion Normalize users power levels

        //region Normalize spaces
        $this->processRoomHierarchy($options, $homeserver);
        //endregion Normalize spaces

        //region Normalize states
        $options->initialState(
            // Process intial states
            $this->prepareRoomInitialStates($options, $eventNamspace, $serviceUserId, $options->isEncrypted())
        );
        //endregion Normalize states

        return $options;
    }

    /**
     * Normalizes the user MXID value.
     *
     * @param UserNamingStrategyInterface $namingStrategy the naming strategy for users
     * @param null|string                 $mxidOrId       the user ID or mxID value
     */
    protected function normalizeUserMxid(UserNamingStrategyInterface $namingStrategy, ?string $mxidOrId): ?string
    {
        $userMxid = $mxidOrId;
        if (null !== $mxidOrId && \is_numeric($mxidOrId)) {
            $userMxid = $namingStrategy->matrixId($mxidOrId);
        }

        return $userMxid;
    }

    /**
     * Prepares the room power levels.
     *
     * @param RoomMessageOptions          $options        the room message options
     * @param UserNamingStrategyInterface $namingStrategy the naming strategy for users
     * @param string                      $serviceUserId  the mxID of the service user
     * @param null|string                 $senderId       the mxID of the sender
     * @param null|string                 $recipientId    the mxId of the recipient
     */
    protected function prepareRoomPowerLevels(
        RoomMessageOptions $options,
        UserNamingStrategyInterface $namingStrategy,
        string $serviceUserId,
        ?string $senderId,
        ?string $recipientId
    ): array {
        $powerLevels = array_merge(['users' => [], 'users_default' => 0], $options->getPowerLevels() ?? []);
        // First, normalize power levels that are alredy set
        if (isset($powerLevels['users'])) {
            $normalizedPowerLevel = [];
            foreach ($powerLevels['users'] ?? [] as $userId => $powerLevel) {
                $normalizedPowerLevel[$this->normalizeUserMxid($namingStrategy, (string) $userId)] = $powerLevel;
            }
            $powerLevels['users'] = $normalizedPowerLevel;
        }
        // Set power levels for service user
        // This power level CANNOT be overriden from the outside.
        if (\in_array($serviceUserId, $options->getInvites())) {
            $powerLevels['users'][$serviceUserId] = 100;
        }
        // When the sender is not the service user
        if ($senderId !== $serviceUserId) {
            // Set power level for sender if it is not already set
            if (null !== $senderId && !isset($powerLevels['users'][$senderId])) {
                // The power level for sender will be maximum value from users default power level and 50
                $powerLevels['users'][$senderId] = \max($powerLevels['users_default'] ?? 0, 50);
            }
        } else {
            // if sender IS service user ID and power level is not set, then we
            // set a default power level of 100
            if (!isset($powerLevels['users'][$senderId])) {
                $powerLevels['users'][$serviceUserId] = 100;
            }
        }
        // Set power level for recipient if it is not already set
        if ($recipientId && !isset($powerLevels['users'][$recipientId])) {
            // The power level for recipient will be users default power level or 0
            $powerLevels['users'][$recipientId] = $powerLevels['users_default'] ?? 0;
        }
        // Walk over all invited users and set power levels for them.
        foreach ($options->getInvites() ?? [] as $userId) {
            // We will set power levels only for not specified users.
            if (!isset($powerLevels['users'][$userId])) {
                // The power level for invtedusers will be users default power level or 0
                $powerLevels['users'][$userId] = $powerLevels['users_default'] ?? 0;
            }
        }

        return $powerLevels;
    }

    /**
     * Prepares the room invites.
     *
     * @param UserNamingStrategyInterface $namingStrategy the naming strategy
     * @param array                       $invites        the list of invites
     * @param string                      $serviceUserId  the mxID of the service user
     * @param null|string                 $senderId       the mxID of the sender
     * @param null|string                 $recipientId    the mxId of the recipient
     */
    protected function preapreRoomInvites(
        UserNamingStrategyInterface $namingStrategy,
        array $invites,
        string $serviceUserId,
        ?string $senderId,
        ?string $recipientId,
        bool $inviteServiceUsers = true
    ): ?array {
        $normalizedInvites = [];
        foreach ($invites as $invite) {
            $normalizedInvites[] = $this->normalizeUserMxid($namingStrategy, (string) $invite);
        }
        // Auto-invite all service users
        if ($inviteServiceUsers && $senderId !== $serviceUserId) {
            $invites[] = $serviceUserId;
        }
        // Invite other users
        // ...

        return \array_values(
            \array_filter(
                \array_unique([
                    ...$invites,
                    $recipientId,
                    ...$normalizedInvites,
                ])
            )
        );
    }

    /**
     * Prepares the room initial states.
     *
     * @param RoomMessageOptions $options       the room message options
     * @param string             $eventNamspace the custom events namespace
     */
    protected function prepareRoomInitialStates(RoomMessageOptions $options, string $eventNamspace, string $serviceUserId, bool $enableEncryption): array
    {
        $profileStates = [];
        if ($this->attachInvitedUsersProfile) {
            $userReferences = $this->matrixConnector->getUserReferenceProvider()->getReferencesByUserMxids(
                \array_filter(
                    \array_unique([$options->getSenderId(), $options->getRecipientId(), ...$options->getInvites() ?? []]),
                    fn (?string $id) => null !== $id && $serviceUserId !== $id
                ),
                false
            );
            foreach ($userReferences as $userReference) {
                $profileStates[] = (new StateEvent())
                    ->setType("{$eventNamspace}.room.member")
                    ->setContent(['profile' => $userReference['profile_room_id']])
                    ->setStateKey("~{$userReference['mxid']}")
                ;
            }
        }
        $additionalStates = [];
        if ($enableEncryption) {
            $additionalStates[] = (new StateEvent())
                ->setType((string) StateEventType::from(StateEventType::ENCRYPTION))
                ->setContent(['algorithm' => (string) EncryptionAlgorithm::from(EncryptionAlgorithm::MEGOLM_AES_SHA2)])
            ;
        }

        return \array_merge(
            [
                (new StateEvent())->setType("{$eventNamspace}.source")->setContent(['source' => 'bus']),
            ],
            $profileStates,
            $additionalStates,
            $options->getInitialState() ?? [],
            $options->getParents() ?? [],
        );
    }

    /**
     * Prepares parent spaces.
     *
     * @param null|StateEvent[] $parentSpaces
     * @param string            $homeserver   the homeserver name
     * @param callable          $roomReader   the room reader
     */
    protected function prepareParentSpaces(?array $parentSpaces, string $homeserver, callable $roomReader): ?array
    {
        if (null === $parentSpaces) {
            return null;
        }

        /** @var StateEvent[] $spacesByRoom */
        $spacesByRoom = [];
        $notValidRoomsIds = [];
        // Walk over spaces to collect ones with invalid space IDs
        foreach ($parentSpaces as $space) {
            $spaceId = $space->getStateKey(); // Get the room ID stored in state key
            $spacesByRoom[$spaceId ?? ''][] = $space; // Group spaces by their IDs
            if (empty($spaceId)) {
                continue;
            }
            // If the ID begins not from `!` then we need to find that ID in the DB
            if (!\str_starts_with($spaceId, '!')) {
                $notValidRoomsIds[] = $spaceId;
            }
        }
        $notValidRoomsIds = \array_unique($notValidRoomsIds); // Make values unique
        $roomReader = \Closure::fromCallable($roomReader); // Make closure from callable
        $foundRoomIds = $roomReader($notValidRoomsIds); // Find spaces IDs
        // Walk over invalid space IDs to update their value
        foreach ($notValidRoomsIds as $reference) {
            if (!isset($foundRoomIds[$reference]) || !isset($spacesByRoom[$reference])) {
                continue;
            }

            foreach ($spacesByRoom[$reference] as $space) {
                $space->setStateKey($foundRoomIds[$reference]); // Replace invalid state key with valid space ID
            }
        }
        $spaces = [];
        // Walk over spaces to add current homeserver to the `via` key
        foreach ($parentSpaces as $space) {
            // If state key is empty - then drop it
            if (empty($space->getStateKey())) {
                continue;
            }

            $content = $space->getContent();
            $content['via'] = array_unique([$homeserver, ...$content['via'] ?? []]);
            $spaces[] = $space;
            $space->setContent($content);
        }

        return !empty($spaces) ? $spaces : null;
    }

    /**
     * Process the room hierarchy.
     *
     * @param RoomMessageOptions $options    the room message options
     * @param string             $homeserver the homeserver name
     */
    protected function processRoomHierarchy(RoomMessageOptions $options, string $homeserver): void
    {
        $options->parents(
            // Here we need to process only parents (aka `spaces`) of the room.
            $this->prepareParentSpaces($options->getParents(), $homeserver, function (array $refs) {
                return $this->resolveSpaceIdsFromNameOfRecordId($this->spacesRepository, $refs);
            })
        );
    }

    /**
     * Resolve space ID values for numeric IDs or space names.
     */
    protected function resolveSpaceIdsFromNameOfRecordId(Model $spacesRepository, array $roomReferences = []): array
    {
        if (empty($roomReferences) || empty($rooms = $spacesRepository->findAllBy(['conditions' => ['idOrName' => $roomReferences]]))) {
            return [];
        }
        $export = [];
        foreach ($rooms as $room) {
            $export[$room['id']] = $room['room_id'];
            $export[$room['name']] = $room['room_id'];
        }

        return \array_intersect_key($export, \array_flip($roomReferences));
    }

    /**
     * Resolve rooms ID values for numeric IDs or space names.
     */
    protected function resolveRoomsIdsFromNameOfRecordId(Model $roomsRepository, array $roomReferences = []): array
    {
        if (empty($roomReferences) || empty($rooms = $roomsRepository->findAllBy(['conditions' => ['ids' => $roomReferences]]))) {
            return [];
        }
        $export = [];
        foreach ($rooms as $room) {
            $export[$room['id']] = $room['room_id'];
        }

        return \array_intersect_key($export, \array_flip($roomReferences));
    }
}
