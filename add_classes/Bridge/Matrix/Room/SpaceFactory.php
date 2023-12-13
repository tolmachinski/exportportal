<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Room;

use App\Bridge\Matrix\Mapping\SpacesNamingStrategyInterface;
use App\Bridge\Matrix\Mapping\UserNamingStrategyInterface;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\Message\SpaceMessageOptions;
use App\Bridge\Matrix\RoomContextProperty;
use App\Bridge\Matrix\RoomPreset;
use App\Bridge\Matrix\RoomType;
use App\Bridge\Matrix\RoomVisibility;
use App\Common\Exceptions\NotSupportedException;
use ExportPortal\Contracts\Chat\Message\RoomMessageOptions as BaseRoomMessageOptions;
use ExportPortal\Matrix\Client\Model\CreateRoomRequest as MatrixRoom;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\RoomReference;
use ExportPortal\Matrix\Client\Model\StateEvent;

final class SpaceFactory extends AbstractRoomFactory
{
    /**
     * {@inheritDoc}
     *
     * @param SpaceMessageOptions $options
     */
    public function create(?string $name, BaseRoomMessageOptions $options): RoomReference
    {
        if (!$this->supports($name, $options)) {
            throw new NotSupportedException('The factory docent support room creation for provided values');
        }

        $processedSpaceOptions = $this->processSpaceOptions(
            $options,
            $this->namingStrategy,
            $this->matrixConnector->getConfig()->getSpacesNamingStrategy(),
            $serviceUser = $this->serviceUser,
            $name,
            $this->configuration->getHomeserverName(),
            $this->configuration->getEventNamespace()
        );
        $space = (new MatrixRoom())
            ->setName($name)
            ->setTopic($processedSpaceOptions->getTopic())
            ->setRoomVersion($processedSpaceOptions->getVersion())
            ->setRoomAliasName($processedSpaceOptions->getAlias())
            ->setVisibility($processedSpaceOptions->getVisibility() ?? (string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->setPreset($processedSpaceOptions->getPreset() ?? (string) RoomPreset::from(RoomPreset::PRIVATE_CHAT))
            ->setInvite($processedSpaceOptions->getInvites())
            ->setInvite3pid($processedSpaceOptions->getThirdPartyInvites())
            ->setInitialState($processedSpaceOptions->getInitialState())
            ->setCreationContent($processedSpaceOptions->getCreationContent())
            ->setPowerLevelContentOverride($processedSpaceOptions->getPowerLevels())
        ;

        try {
            // If sender is not service user, then we need to login as this user.
            $sender = $serviceUser;
            if (null !== $processedSpaceOptions->getSenderId() && $processedSpaceOptions->getSenderId() !== $serviceUser->getUserId()) {
                $sender = $this->matrixConnector->loginAsMatrixUser($serviceUser, $processedSpaceOptions->getSenderId());
            }

            // Create space
            return $this->doCreateRoom($this->matrixClient, $sender, $space);
        } finally {
            // After that we need to logout current users but only if it is not service user.
            try {
                if ($sender !== $serviceUser) {
                    $this->matrixConnector->logoutUser($sender);
                }
            } catch (\Throwable $e) {
                // Just roll with it - we don't need to bother with logout.
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param SpaceMessageOptions $options
     */
    public function supports(?string $name, BaseRoomMessageOptions $options): bool
    {
        if (null === $name && null === $options) {
            return false;
        }

        return $options instanceof SpaceMessageOptions;
    }

    /**
     * Process room options into accepted form. Returns new instance of options.
     */
    protected function processSpaceOptions(
        SpaceMessageOptions $originalOptions,
        UserNamingStrategyInterface $namingStrategy,
        SpacesNamingStrategyInterface $spacesNamingStrategy,
        AuthenticatedUser $serviceUser,
        string $subject,
        string $homeserver,
        string $eventNamspace
    ): SpaceMessageOptions {
        $options = $this->processRoomOptions($originalOptions, $namingStrategy, $serviceUser, $homeserver, $eventNamspace);

        //region Normalize space alias
        $name = $subject;
        $alias = $options->getAlias();
        if (null === $alias) {
            $options->alias(
                $spacesNamingStrategy->spaceName($name)
            );
        }
        //endregion Normalize space alias

        //region Normalize creation content
        $options->creationContent(\array_merge(
            $options->getCreationContent() ?? [],
            [
                (string) RoomContextProperty::from(RoomContextProperty::TYPE) => (string) RoomType::from(RoomType::SPACE),
            ]
        ));
        //endregion Normalize creation content

        return $options;
    }

    /**
     * Prepares the room power levels.
     *
     * {@inheritDoc}
     */
    protected function prepareRoomPowerLevels(
        RoomMessageOptions $options,
        UserNamingStrategyInterface $namingStrategy,
        string $serviceUserId,
        ?string $senderId,
        ?string $recipientId
    ): array {
        return \array_merge(
            parent::prepareRoomPowerLevels($options, $namingStrategy, $serviceUserId, $senderId, $recipientId),
            // Raising power levels for sending messages to prevent space clogging from messages
            // Such practice is strictly discouraged: https://github.com/matrix-org/matrix-doc/blob/master/proposals/1772-groups-as-rooms.md#proposal
            ['events_default' => 100],
        );
    }

    /**
     * Prepares the space children.
     *
     * @param null|StateEvent[] $childRooms
     */
    protected function prepareChildren(?array $childRooms, string $homeserver, callable $roomReader): ?array
    {
        if (null === $childRooms) {
            return null;
        }

        /** @var StateEvent[] $childrenByRoom */
        $childrenByRoom = [];
        $notValidRoomsIds = [];
        // Walk over chilren to collect ones with invalid child IDs
        foreach ($childRooms as $child) {
            $childId = $child->getStateKey(); // Get the room ID stored in state key
            $childrenByRoom[$childId ?? ''][] = $child; // Group children by their IDs
            if (empty($childId)) {
                continue;
            }
            // If the ID begins not from `!` then we need to find that ID in the DB
            if (!\str_starts_with($childId, '!')) {
                $notValidRoomsIds[] = $childId;
            }
        }
        $notValidRoomsIds = \array_unique($notValidRoomsIds); // Make values unique
        $roomReader = \Closure::fromCallable($roomReader); // Make closure from callable
        $foundRoomIds = $roomReader($notValidRoomsIds); // Find IDs
        // Walk over invalid room IDs to update their value
        foreach ($notValidRoomsIds as $reference) {
            if (!isset($foundRoomIds[$reference]) || !isset($childrenByRoom[$reference])) {
                continue;
            }

            foreach ($childrenByRoom[$reference] as $child) {
                $child->setStateKey($foundRoomIds[$reference]); // Replace invalid state key with valid room ID
            }
        }
        $children = [];
        // Walk over children to add current homeserver to the `via` key
        foreach ($childRooms as $child) {
            // If state key is empty - then drop it
            if (empty($child->getStateKey())) {
                continue;
            }

            $content = $child->getContent();
            $content['via'] = array_unique([$homeserver, ...$content['via'] ?? []]);
            $children[] = $child;
            $child->setContent($content);
        }

        return !empty($children) ? $children : null;
    }

    /**
     * {@inheritDoc}
     *
     * @param SpaceMessageOptions $options the room message options
     */
    protected function processRoomHierarchy(RoomMessageOptions $options, string $homeserver): void
    {
        parent::processRoomHierarchy($options, $homeserver);
        // Process child rooms.
        $options->childRooms(
            $this->prepareChildren($options->getChildRooms(), $homeserver, function (array $refs) {
                return $this->resolveRoomsIdsFromNameOfRecordId($this->roomsRepository, $refs);
            })
        );
        // Process child spaces.
        $options->childSpaces(
            $this->prepareChildren($options->getChildSpaces(), $homeserver, function (array $refs) {
                return $this->resolveSpaceIdsFromNameOfRecordId($this->spacesRepository, $refs);
            })
        );
    }

    /**
     * Prepares the room initial states.
     *
     * @param SpaceMessageOptions $options       the room message options
     * @param string              $eventNamspace the custom events namespace
     */
    protected function prepareRoomInitialStates(RoomMessageOptions $options, string $eventNamspace, string $serviceUserId, bool $enableEncryption): array
    {
        return \array_merge(
            parent::prepareRoomInitialStates($options, $eventNamspace, $serviceUserId, $enableEncryption),
            $options->getChildSpaces() ?? [],
            $options->getChildRooms() ?? [],
        );
    }
}
