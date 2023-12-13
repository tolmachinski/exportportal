<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

/**
 * A set of rules for determining the matrix user, profile and server notices rooms names.
 */
interface UserNamingStrategyInterface
{
    /**
     * Returns a matrix user name for a user ID.
     */
    public function userName(string $userId): string;

    /**
     * Returns a matrix user ID for a user ID.
     */
    public function matrixId(string $userId): string;

    /**
     * Returns a profile room name for a user ID.
     */
    public function profileRoomNameForUserId(string $userId): string;

    /**
     * Returns a profile room name for a matrix user name.
     */
    public function profileRoomNameForUserName(string $userName): string;

    /**
     * Returns a profile room name for a matrix user ID.
     */
    public function profileRoomNameForMatrixId(string $userName): string;

    /**
     * Returns a profile room alias for a user ID.
     */
    public function profileRoomAliasForUserId(string $userId): string;

    /**
     * Returns a profile room alias for a matrix user name.
     */
    public function profileRoomAliasForUserName(string $userName): string;

    /**
     * Returns a profile room alias for a matrix user ID.
     */
    public function profileRoomAliasForMatrixId(string $userName): string;

    /**
     * Returns a server notices room name for a user ID.
     */
    public function serverNoticesRoomNameForUserId(string $userId): string;

    /**
     * Returns a server notices room name for a matrix user name.
     */
    public function serverNoticesRoomNameForUserName(string $userName): string;

    /**
     * Returns a server notices room name for a matrix user ID.
     */
    public function serverNoticesRoomNameForMatrixId(string $userName): string;

    /**
     * Returns a server notices room alias for a user ID.
     */
    public function serverNoticesRoomAliasForUserId(string $userId): string;

    /**
     * Returns a server notices room alias for a matrix user name.
     */
    public function serverNoticesRoomAliasForUserName(string $userName): string;

    /**
     * Returns a server notices room alias for a matrix user ID.
     */
    public function serverNoticesRoomAliasForMatrixId(string $userName): string;

    /**
     * Returns a cargo room name for a user ID.
     */
    public function cargoNoticesRoomNameForUserId(string $userId): string;

    /**
     * Returns a cargo room name for a matrix user name.
     */
    public function cargoNoticesRoomNameForUserName(string $userName): string;

    /**
     * Returns a cargo room name for a matrix user ID.
     */
    public function cargoNoticesRoomNameForMatrixId(string $userName): string;

    /**
     * Returns a cargo room alias for a user ID.
     */
    public function cargoNoticesRoomAliasForUserId(string $userId): string;

    /**
     * Returns a cargo room alias for a matrix user name.
     */
    public function cargoNoticesRoomAliasForUserName(string $userName): string;

    /**
     * Returns a cargo room alias for a matrix user ID.
     */
    public function cargoNoticesRoomAliasForMatrixId(string $userName): string;
}
