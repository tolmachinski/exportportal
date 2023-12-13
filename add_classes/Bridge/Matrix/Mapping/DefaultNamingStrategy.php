<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

/**
 * @deprecated Use UserDefaultNamingStrategy instead
 */
class DefaultNamingStrategy extends UserDefaultNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function spaceName(string $name): string
    {
        return (string) \strtolower($name);
    }

    /**
     * {@inheritDoc}
     */
    public function spaceAlias(string $name): string
    {
        return \sprintf('#%s:%s', $this->spaceName($name), $this->homeserver);
    }
}
