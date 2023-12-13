<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use App\Bridge\Matrix\Mapping\NamingStrategyInterface;
use App\Bridge\Matrix\Mapping\SpacesNamingStrategyInterface;
use App\Bridge\Matrix\Mapping\UserNamingStrategyInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Configuration
{
    /**
     * The naming strategy.
     */
    private NamingStrategyInterface $namingStrategy;

    /**
     * The user naming strategy.
     */
    private UserNamingStrategyInterface $userNamingStrategy;

    /**
     * The spaces naming strategy.
     */
    private SpacesNamingStrategyInterface $spaceNamingStrategy;

    /**
     * The logger.
     */
    private ?LoggerInterface $logger;

    /**
     * The Matrix service account user (without homeserver).
     */
    private string $user;

    /**
     * The Matrix service account user ID.
     */
    private string $userId;

    /**
     * The Matrix service account password.
     */
    private string $password;

    /**
     * The Matrix service account access token.
     */
    private string $accessToken;

    /**
     * The name of the Matrix homesever.
     */
    private string $homeserverName;

    /**
     * The Matrix homesever host name.
     */
    private string $homeserverHost;

    /**
     * The sync version.
     */
    private string $syncVersion;

    /**
     * The event namespace.
     */
    private string $eventNamespace;

    /**
     * The local machine device ID.
     */
    private ?string $deviceId;

    /**
     * The flag that indicates that encryption is enabled.
     */
    private bool $encryptionEnabled = false;

    /**
     * Get the naming strategy.
     *
     * @deprecated use static::getUserNamingStrategy()
     */
    public function getNamingStrategy(): NamingStrategyInterface
    {
        if (!isset($this->namingStrategy)) {
            throw new RuntimeException(
                \sprintf('The "%s::$namingStrategy" parameter must be set first before using it.', self::class)
            );
        }

        return $this->namingStrategy;
    }

    /**
     * Set the naming strategy.
     *
     * @deprecated use static::setUserNamingStrategy()
     */
    public function setNamingStrategy(NamingStrategyInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    /**
     * Get the user naming strategy.
     */
    public function getUserNamingStrategy(): UserNamingStrategyInterface
    {
        if (!isset($this->userNamingStrategy)) {
            throw new RuntimeException(
                \sprintf('The "%s::$userNamingStrategy" parameter must be set first before using it.', self::class)
            );
        }

        return $this->userNamingStrategy;
    }

    /**
     * Set the user naming strategy.
     */
    public function setUserNamingStrategy(UserNamingStrategyInterface $userNamingStrategy): self
    {
        $this->userNamingStrategy = $userNamingStrategy;

        return $this;
    }

    /**
     * Get the spaces naming strategy.
     */
    public function getSpacesNamingStrategy(): SpacesNamingStrategyInterface
    {
        if (!isset($this->spaceNamingStrategy)) {
            throw new RuntimeException(
                \sprintf('The "%s::$spaceNamingStrategy" parameter must be set first before using it.', self::class)
            );
        }

        return $this->spaceNamingStrategy;
    }

    /**
     * Set the spaces naming strategy.
     */
    public function setSpacesNamingStrategy(SpacesNamingStrategyInterface $spaceNamingStrategy): self
    {
        $this->spaceNamingStrategy = $spaceNamingStrategy;

        return $this;
    }

    /**
     * Get the logger.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set the logger.
     */
    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get the admin's account Matrix username.
     *
     * @deprecated
     * @see self::geUserId()
     *
     * @uses self::geUserId()
     */
    public function getAdminUsername(): string
    {
        return $this->getUserId();
    }

    /**
     * Set the admin's account Matrix username.
     *
     * @deprecated
     * @see self::setUserId()
     *
     * @uses self::setUserId()
     */
    public function setAdminUsername(string $adminUsername): self
    {
        return $this->setUserId($adminUsername);
    }

    /**
     * Get the admin's Matrix account password.
     *
     * @deprecated
     * @see self::getPassword()
     *
     * @uses self::getPassword()
     */
    public function getAdminPassword(): string
    {
        return $this->getPassword();
    }

    /**
     * Set the admin's Matrix account password.
     *
     * @deprecated
     * @see self::setPassword()
     *
     * @uses self::setPassword()
     */
    public function setAdminPassword(string $adminPassword): self
    {
        return $this->setPassword($adminPassword);
    }

    /**
     * Get the Matrix service account user (without homeserver).
     */
    public function getUser(): string
    {
        if (!isset($this->user)) {
            throw new RuntimeException(
                \sprintf('The "%s::$user" parameter must be set first before using it.', self::class)
            );
        }

        return $this->user;
    }

    /**
     * Set the Matrix service account user (without homeserver).
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the Matrix Matrix service account user ID.
     */
    public function getUserId(): string
    {
        if (!isset($this->userId)) {
            throw new RuntimeException(
                \sprintf('The "%s::$userId" parameter must be set first before using it.', self::class)
            );
        }

        return $this->userId;
    }

    /**
     * Set the Matrix service account user ID.
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the Matrix service account password.
     */
    public function getPassword(): string
    {
        if (!isset($this->password)) {
            throw new RuntimeException(
                \sprintf('The "%s::$password" parameter must be set first before using it.', self::class)
            );
        }

        return $this->password;
    }

    /**
     * Set the Matrix service account password.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the Matrix service account access token.
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set the Matrix service account access token.
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the name of the Matrix homesever.
     */
    public function getHomeserverName(): string
    {
        if (!isset($this->homeserverName)) {
            throw new RuntimeException(
                \sprintf('The "%s::$homeserverHame" parameter must be set first before using it.', self::class)
            );
        }

        return $this->homeserverName;
    }

    /**
     * Set the name of the Matrix homesever.
     */
    public function setHomeserverName(string $homeserverName): self
    {
        $this->homeserverName = $homeserverName;

        return $this;
    }

    /**
     * Get the Matrix homesever host name.
     */
    public function getHomeserverHost(): string
    {
        if (!isset($this->homeserverHost)) {
            throw new RuntimeException(
                \sprintf('The "%s::$homeserverHost" parameter must be set first before using it.', self::class)
            );
        }

        return $this->homeserverHost;
    }

    /**
     * Set the Matrix homesever host name.
     */
    public function setHomeserverHost(string $homeserverHost): self
    {
        $this->homeserverHost = $homeserverHost;

        return $this;
    }

    /**
     * Get the sync version.
     */
    public function getSyncVersion(): string
    {
        if (!isset($this->syncVersion)) {
            throw new RuntimeException(
                \sprintf('The "%s::$syncVersion" parameter must be set first before using it.', self::class)
            );
        }

        return $this->syncVersion;
    }

    /**
     * Set the sync version.
     */
    public function setSyncVersion(string $syncVersion): self
    {
        $this->syncVersion = $syncVersion;

        return $this;
    }

    /**
     * Get the event namespace.
     */
    public function getEventNamespace(): string
    {
        if (!isset($this->eventNamespace)) {
            throw new RuntimeException(
                \sprintf('The "%s::$eventNamespace" parameter must be set first before using it.', self::class)
            );
        }

        return $this->eventNamespace;
    }

    /**
     * Set the event namespace.
     */
    public function setEventNamespace(string $eventNamespace): self
    {
        $this->eventNamespace = $eventNamespace;

        return $this;
    }

    /**
     * Get the local machine device ID.
     */
    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    /**
     * Set the local machine device ID.
     */
    public function setDeviceId(?string $deviceId): self
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * Set the flag that indicates that encryption is enabled.
     */
    public function setEncryptionEnabled(bool $encryptionEnabled): self
    {
        $this->encryptionEnabled = $encryptionEnabled;

        return $this;
    }

    /**
     * Determine if encryption is enabled.
     */
    public function isEncryptionEnabled(): bool
    {
        return $this->encryptionEnabled;
    }
}
