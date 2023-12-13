<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

class UserDefaultNamingStrategy implements UserNamingStrategyInterface
{
    /**
     * The homeserver name.
     */
    protected string $homeserver;

    /**
     * The mastrix user name prefix.
     */
    protected string $userNamePrefix;

    /**
     * The profile room name prefix.
     */
    protected string $profileRoomPrefix;

    /**
     * The server notices name prefix.
     */
    protected string $serverNoticesRoomPrefix;

    /**
     * The cargo name prefix.
     */
    protected string $cargoNoticesRoomPrefix;

    public function __construct(
        string $homeserver,
        $userNamePrefix = 'ep-user',
        $profileRoomPrefix = 'profile',
        $serverNoticesRoomPrefix = 'notices',
        $cargoNoticesRoomPrefix = 'cargo'
    ) {
        $this->homeserver = $homeserver;
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
        return \sprintf('%s.%s', $this->userNamePrefix, $userId);
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
