<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Room;

use ExportPortal\Contracts\Chat\Message\RoomMessageOptions;
use ExportPortal\Matrix\Client\Model\RoomReference;

interface RoomFactoryInterface
{
    /**
     * Creates the matrix room.
     */
    public function create(?string $name, RoomMessageOptions $options): RoomReference;

    /**
     * Determine if factory supports creation of the room for the provided options.
     */
    public function supports(?string $name, RoomMessageOptions $options): bool;
}
