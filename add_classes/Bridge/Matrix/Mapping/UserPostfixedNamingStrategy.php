<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

class UserPostfixedNamingStrategy implements UserNamingStrategyInterface
{
    /**
     * The decorated naming strategy.
     */
    protected UserNamingStrategyInterface $namingStrategy;

    /**
     * The postfix.
     */
    protected string $postfix;

    public function __construct(UserNamingStrategyInterface $namingStrategy, string $postfix)
    {
        $this->postfix = $postfix;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function userName(string $userId): string
    {
        return \sprintf('%s.%s', $this->namingStrategy->userName($userId), $this->postfix);
    }

    /**
     * {@inheritDoc}
     */
    public function matrixId(string $userId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->matrixId($userId));
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForUserId(string $userId): string
    {
        return "{$this->namingStrategy->profileRoomNameForUserId($userId)}.{$this->postfix}";
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->profileRoomNameForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomNameForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->profileRoomNameForMatrixId($matrixId));
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForUserId(string $userId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->profileRoomAliasForUserId($userId));
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->profileRoomAliasForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function profileRoomAliasForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->profileRoomAliasForMatrixId($matrixId));
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForUserId(string $userId): string
    {
        return "{$this->namingStrategy->serverNoticesRoomNameForUserId($userId)}.{$this->postfix}";
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->serverNoticesRoomNameForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomNameForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->serverNoticesRoomNameForMatrixId($matrixId));
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForUserId(string $userId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->serverNoticesRoomAliasForUserId($userId));
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->serverNoticesRoomAliasForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function serverNoticesRoomAliasForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->serverNoticesRoomAliasForMatrixId($matrixId));
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForUserId(string $userId): string
    {
        return "{$this->namingStrategy->cargoNoticesRoomNameForUserId($userId)}.{$this->postfix}";
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->cargoNoticesRoomNameForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomNameForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->cargoNoticesRoomNameForMatrixId($matrixId));
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForUserId(string $userId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->cargoNoticesRoomAliasForUserId($userId));
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForUserName(string $userName): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->cargoNoticesRoomAliasForUserName($userName));
    }

    /**
     * {@inheritDoc}
     */
    public function cargoNoticesRoomAliasForMatrixId(string $matrixId): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->cargoNoticesRoomAliasForMatrixId($matrixId));
    }

    /**
     * Adds prefix to the name.
     */
    protected function addPrefixToTheName(string $name): string
    {
        return \preg_replace(\sprintf('/^(.+?)(\\.%s)?\\:(.+)$/', \preg_quote($this->postfix)), "$1.{$this->postfix}:$3", $name);
    }
}
