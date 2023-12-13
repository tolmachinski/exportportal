<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Message;

use ExportPortal\Contracts\Chat\Message\RoomMessageOptions as BaseRoomMessageOptions;

/**
 * @author Anton Zencenco
 */
class RoomMessageOptions extends BaseRoomMessageOptions
{
    use MessageOptionsTrait;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['parents'])) {
            $this->parents($options['parents']);
        }
        if (isset($options['initialState'])) {
            $this->initialState($options['initialState']);
        }
        if (isset($options['thirdPartyInvites'])) {
            $this->thirdPartyInvites($options['thirdPartyInvites']);
        }

        $this->inviteServiceUsers($options['inviteServiceUsers'] ?? true);
    }

    /**
     * Set the flag for room direct mode.
     *
     * @return $this
     */
    public function direct(bool $direct): self
    {
        $this->options['direct'] = $direct;

        return $this;
    }

    /**
     * Determine if room is direct.
     */
    public function isDirect(): bool
    {
        return $this->options['direct'] ?? false;
    }
}
