<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs;

/**
 * @deprecated in favor of Uril::idToContext
 */
final class UserContextBuilder implements ContextBuilderInterface
{
    /**
     * The HTTP referer value.
     *
     * @var string
     */
    private $referer;

    /**
     * The context user.
     *
     * @var null|int|string
     */
    private $contextUser;

    /**
     * The prepared context.
     *
     * @var array
     */
    private $preparedContext = [];

    /**
     * Creates instance of user context builder.
     */
    public function __construct(string $referer)
    {
        $this->referer = $referer;
    }

    /**
     * Set the context user.
     *
     * @param null|int|string $contextUser
     *
     * @return self
     */
    public function setContextUser($contextUser)
    {
        $this->contextUser = $contextUser;

        return $this;
    }

    /**
     * Returns the context.
     *
     * @return array
     */
    public function buildContext(): ContextBuilderInterface
    {
        $this->preparedContext = [
            'id'     => $this->contextUser,
            'origin' => $this->referer,
        ];

        return $this;
    }

    /**
     * Returns the context.
     */
    public function getContext(): array
    {
        return $this->preparedContext;
    }
}
