<?php

declare(strict_types=1);

namespace App\Users;

use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\Group\GroupType;

trait GroupMemberTrait
{
    /**
     * The group's name.
     */
    private ?string $groupName;

    /**
     * The group's type.
     */
    private ?GroupType $groupType;

    /**
     * The group's alias.
     */
    private ?GroupAlias $groupAlias;

    /**
     * Checks if person group name exists.
     */
    public function hasGroupName(): bool
    {
        return null !== $this->groupName;
    }

    /**
     * Returns the group name of the person.
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * Returns an instance with the specified group name.
     *
     * @return static
     */
    public function withGroupName(?string $groupName)
    {
        $instance = clone $this;
        $instance->groupName = $groupName;

        return $instance;
    }

    /**
     * Return an instance without group name.
     *
     * @return static
     */
    public function withoutGroupName()
    {
        $instance = clone $this;
        $instance->groupName = null;

        return $instance;
    }

    /**
     * Checks if person group type exists.
     */
    public function hasGroupType(): bool
    {
        return null !== $this->groupType;
    }

    /**
     * Returns the group type of the person.
     */
    public function getGroupType(): ?GroupType
    {
        return $this->groupType;
    }

    /**
     * Returns an instance with the specified group type.
     *
     * @return static
     */
    public function withGroupType(?GroupType $groupType)
    {
        $instance = clone $this;
        $instance->groupType = $groupType;

        return $instance;
    }

    /**
     * Return an instance without group type.
     *
     * @return static
     */
    public function withoutGroupType()
    {
        $instance = clone $this;
        $instance->groupType = null;

        return $instance;
    }

    /**
     * Checks if person group alias exists.
     */
    public function hasGroupAlias(): bool
    {
        return null !== $this->groupAlias;
    }

    /**
     * Returns the group alias of the person.
     */
    public function getGroupAlias(): ?GroupAlias
    {
        return $this->groupAlias;
    }

    /**
     * Returns an instance with the specified group alias.
     *
     * @return static
     */
    public function withGroupAlias(?GroupAlias $groupAlias)
    {
        $instance = clone $this;
        $instance->groupAlias = $groupAlias;

        return $instance;
    }

    /**
     * Return an instance without group alias.
     *
     * @return static
     */
    public function withoutGroupAlias()
    {
        $instance = clone $this;
        $instance->groupAlias = null;

        return $instance;
    }
}
