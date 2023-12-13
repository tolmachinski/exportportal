<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

use Ramsey\Uuid\Uuid;

class UserUuidNamingStrategy implements UserNamingStrategyInterface
{
    /**
     * The mastrix user name prefix.
     */
    protected string $userNamePrefix;

    /**
     * The profile room name prefix.
     */
    protected string $profileRoomPrefix;

    /**
     * The server notices room name prefix.
     */
    protected string $serverNoticesRoomPrefix;

    /**
     * The cargo room name prefix.
     */
    protected string $cargoNoticesRoomPrefix;

    /**
     * The UUID namespace.
     */
    protected string $uuidNamespace;

    /**
     * The name of the homeserver.
     */
    protected string $homeserver;

    public function __construct(
        string $uuidNamespace,
        string $homeserver,
        string $userNamePrefix = 'ep-user',
        string $profileRoomPrefix = 'profile',
        string $serverNoticesRoomPrefix = 'notices',
        string $cargoNoticesRoomPrefix = 'cargo'
    ) {
        $this->homeserver = $homeserver;
        $this->uuidNamespace = $uuidNamespace;
        $this->userNamePrefix = $userNamePrefix;
        $this->profileRoomPrefix = $profileRoomPrefix;
        $this->serverNoticesRoomPrefix = $serverNoticesRoomPrefix;
        $this->cargoNoticesRoomPrefix = $cargoNoticesRoomPrefix;
    }

    /**
     * {@inheritDoc}
     */
    public function userName(string $userId): string
    {
        return \sprintf('%s.%s', $this->userNamePrefix, Uuid::uuid5($this->uuidNamespace, "users/{$userId}"));
    }

    /**
     * {@inheritDoc}
     */
    public function matrixId(string $userId): string
    {
        return "@{$this->userName($userId)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForUserId(string $userId): string
    {
        return "{$this->profileRoomPrefix}.{$this->userName($userId)}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForUserName(string $userName): string
    {
        return "{$this->profileRoomPrefix}.{$userName}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+):(.+)/', "{$this->profileRoomPrefix}.$1", $matrixId);
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForUserId(string $userId): string
    {
        return "#{$this->profileRoomNameForUserId($userId)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForUserName(string $userName): string
    {
        return "#{$this->profileRoomNameForUserName($userName)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+)/', "#{$this->profileRoomPrefix}.$1", $matrixId);
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForUserId(string $userId): string
    {
        return "{$this->serverNoticesRoomPrefix}.{$this->userName($userId)}";
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForUserName(string $userName): string
    {
        return "{$this->serverNoticesRoomPrefix}.{$userName}";
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+):(.+)/', "{$this->serverNoticesRoomPrefix}.$1", $matrixId);
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForUserId(string $userId): string
    {
        return "#{$this->serverNoticesRoomNameForUserId($userId)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForUserName(string $userName): string
    {
        return "#{$this->serverNoticesRoomNameForUserName($userName)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+)/', "#{$this->serverNoticesRoomPrefix}.$1", $matrixId);
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForUserId(string $userId): string
    {
        return "{$this->cargoNoticesRoomPrefix}.{$this->userName($userId)}";
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForUserName(string $userName): string
    {
        return "{$this->cargoNoticesRoomPrefix}.{$userName}";
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+):(.+)/', "{$this->cargoNoticesRoomPrefix}.$1", $matrixId);
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForUserId(string $userId): string
    {
        return "#{$this->cargoNoticesRoomNameForUserId($userId)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForUserName(string $userName): string
    {
        return "#{$this->cargoNoticesRoomNameForUserName($userName)}:{$this->homeserver}";
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForMatrixId(string $matrixId): string
    {
        return \preg_replace('/^\@(.+)/', "#{$this->cargoNoticesRoomPrefix}.$1", $matrixId);
    }
}
