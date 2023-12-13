<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\Company;

use App\Common\Contracts\Group\GroupType;

/**
 * Command that is used to update company video.
 *
 * @author Anton Zencenco
 */
final class UpdateCompanyVideo
{
    /**
     * The user's group type.
     */
    private GroupType $groupType;

    /**
     * The company's ID value.
     */
    private int $companyId;

    /**
     * The URL of the new video.
     */
    private ?string $url;

    /**
     * @param int       $companyId the company's ID value
     * @param GroupType $groupType the user's group type
     * @param string    $url       the URL of the new video
     */
    public function __construct(int $companyId, GroupType $groupType, ?string $url)
    {
        $this->url = $url;
        $this->companyId = $companyId;
        $this->groupType = $groupType;
    }

    /**
     * Get the company ID value.
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Set the company ID value.
     */
    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
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

    /**
     * Get the URL of the new video.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set the URL of the new video.
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
