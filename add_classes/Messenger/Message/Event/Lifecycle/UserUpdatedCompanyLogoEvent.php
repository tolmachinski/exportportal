<?php

declare(strict_types=1);

namespace App\Messenger\Message\Event\Lifecycle;

use App\Common\Contracts\Group\GroupType;

/**
 * Event triggered when user updated the company logo.
 *
 * @deprecated `v2.40.4.5` in favor of the `App\Messenger\Message\Event\Lifecycle\UserUpdatedSellerCompanyLogoEvent`. Delete this class after period of time.
 */
final class UserUpdatedCompanyLogoEvent extends UserUpdatedSellerCompanyLogoEvent
{
    /**
     * The user's group type.
     */
    protected GroupType $groupType;

    /**
     * The company's ID value.
     */
    protected int $companyId;

    /**
     * The logo path.
     */
    protected ?string $logoPath;

    /**
     * @param int $userId the user ID value
     */
    public function __construct(int $userId, int $companyId, GroupType $groupType, ?string $logoPath = null)
    {
        parent::__construct($userId, $companyId, $logoPath);

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
