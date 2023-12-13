<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Lifecycle;

use App\Common\Contracts\Group\GroupType;

/**
 * Event triggered when user's company was updated.
 *
 * @deprecated `v2.40.4.5` in favor of the `App\Messenger\Message\Event\Lifecycle\UserUpdatedSellerCompanyEvent`. Delete this class after period of time.
 */
class UserUpdatedCompanyEvent extends UserUpdatedSellerCompanyEvent
{
    /**
     * The user's group type.
     */
    protected GroupType $groupType;

    /**
     * @param int $userId the user ID value
     */
    public function __construct(int $userId, int $companyId, GroupType $groupType)
    {
        parent::__construct($userId, $companyId);

        $this->groupType = $groupType;
    }

    /**
     * Get the user's group type.
     */
    public function getGroupType(): GroupType
    {
        return $this->groupType;
    }

    /**
     * Set the user's group type.
     *
     * @return $this
     */
    public function setGroupType(GroupType $groupType): self
    {
        $this->groupType = $groupType;

        return $this;
    }
}
