<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

use Symfony\Component\String\UnicodeString;

/**
 * A set of rules for matrix spaces names.
 */
class DefaultSpacesNamingStrategy implements SpacesNamingStrategyInterface
{
    /**
     * The homeserver name.
     */
    protected string $homeserver;

    /**
     * @param string $homeserver the homeserver name
     */
    public function __construct(string $homeserver)
    {
        $this->homeserver = $homeserver;
    }

    /**
     * {@inheritDoc}
     */
    public function spaceName(string $name): string
    {
        return (new UnicodeString($name))->snake()->replace('_', '-')->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function spaceAlias(string $name): string
    {
        return \sprintf('#%s:%s', $name, $this->homeserver);
    }
}
