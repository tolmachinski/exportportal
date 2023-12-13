<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

use Ramsey\Uuid\Uuid;

/**
 * @deprecated Use UserUuidNamingStrategy instead
 */
class UuidNamingStrategy extends UserUuidNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function spaceName(string $name): string
    {
        return (string) Uuid::uuid5($this->uuidNamespace, "space/{$name}");
    }

    /**
     * {@inheritDoc}
     */
    public function spaceAlias(string $name): string
    {
        return \sprintf('#%s:%s', $this->spaceName($name), $this->homeserver);
    }
}
