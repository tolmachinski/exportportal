<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Room;

use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\RoomPreset;
use App\Bridge\Matrix\RoomVisibility;
use App\Common\Exceptions\NotSupportedException;
use ExportPortal\Contracts\Chat\Message\RoomMessageOptions as BaseRoomMessageOptions;
use ExportPortal\Matrix\Client\Model\CreateRoomRequest as MatrixRoom;
use ExportPortal\Matrix\Client\Model\RoomReference;

final class RoomFactory extends AbstractRoomFactory
{
    /**
     * {@inheritDoc}
     *
     * @param RoomMessageOptions $options
     */
    public function create(?string $name, BaseRoomMessageOptions $options): RoomReference
    {
        if (!$this->supports($name, $options)) {
            throw new NotSupportedException('The factory docent support room creation for provided values');
        }

        $processedRoomOptions = $this->processRoomOptions(
            $options,
            $this->namingStrategy,
            $serviceUser = $this->serviceUser,
            $this->configuration->getHomeserverName(),
            $this->configuration->getEventNamespace()
        );
        // Make room
        $room = (new MatrixRoom())
            ->setName($name ?? null)
            ->setTopic($processedRoomOptions->getTopic())
            ->setRoomVersion($processedRoomOptions->getVersion())
            ->setRoomAliasName($processedRoomOptions->getAlias())
            ->setVisibility($processedRoomOptions->getVisibility() ?? (string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->setPreset($processedRoomOptions->getPreset() ?? (string) RoomPreset::from(RoomPreset::PRIVATE_CHAT))
            ->setInvite($processedRoomOptions->getInvites())
            ->setInvite3pid($processedRoomOptions->getThirdPartyInvites())
            ->setInitialState($processedRoomOptions->getInitialState())
            ->setCreationContent($processedRoomOptions->getCreationContent())
            ->setPowerLevelContentOverride($processedRoomOptions->getPowerLevels())
        ;

        try {
            // If sender is not service user, then we need to login as this user.
            $sender = $serviceUser;
            if (null !== $processedRoomOptions->getSenderId() && $processedRoomOptions->getSenderId() !== $serviceUser->getUserId()) {
                $sender = $this->matrixConnector->loginAsMatrixUser($serviceUser, $processedRoomOptions->getSenderId());
            }

            // Create room
            return $this->doCreateRoom($this->matrixClient, $sender, $room);
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
     * @param RoomMessageOptions $options
     */
    public function supports(?string $name, BaseRoomMessageOptions $options): bool
    {
        if (null === $name && null === $options) {
            return false;
        }

        return $options instanceof RoomMessageOptions;
    }
}
