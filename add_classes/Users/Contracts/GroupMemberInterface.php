<?php

declare(strict_types=1);

namespace App\Users\Contracts;

use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\Group\GroupType;

interface GroupMemberInterface
{
    /**
     * Checks if person group name exists.
     */
    public function hasGroupName(): bool;

    /**
     * Returns the group name of the person.
     */
    public function getGroupName(): ?string;

    /**
     * Returns an instance with the specified group name.
     *
     * @return static
     */
    public function withGroupName(?string $group);

    /**
     * Return an instance without group name.
     *
     * @return static
     */
    public function withoutGroupName();

    /**
     * Checks if person group type exists.
     */
    public function hasGroupType(): bool;

    /**
     * Returns the group type of the person.
     */
    public function getGroupType(): ?GroupType;

    /**
     * Returns an instance with the specified group type.
     *
     * @return static
     */
    public function withGroupType(?GroupType $group);

    /**
     * Return an instance without group type.
     *
     * @return static
     */
    public function withoutGroupType();

    /**
     * Checks if person group alias exists.
     */
    public function hasGroupAlias(): bool;

    /**
     * Returns the group alias of the person.
     */
    public function getGroupAlias(): ?GroupAlias;

    /**
     * Returns an instance with the specified group alias.
     *
     * @return static
     */
    public function withGroupAlias(?GroupAlias $group);

    /**
     * Return an instance without group alias.
     *
     * @return static
     */
    public function withoutGroupAlias();
}
