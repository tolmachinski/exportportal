<?php

declare(strict_types=1);

namespace App\Envelope\Message;

trait AccessRulesAwareTrait
{
    /**
     * The list of access rules that will be used to filters managers that will receive notifications.
     */
    private array $accessRulesList = [];

    /**
     * Get the list of access rules that will be used to filters managers that will receive notifications.
     */
    public function getAccessRulesList(): array
    {
        return $this->accessRulesList;
    }

    /**
     * Get the instance of the class with list of access rules that will be used to filters managers that will receive notifications.
     *
     * @return self
     */
    public function withAccessRulesList(array $list)
    {
        $new = clone $this;
        $new->accessRulesList = $list;

        return $new;
    }

    /**
     * Get the instance of the class without list of access rules that will be used to filters managers that will receive notifications.
     */
    public function withoutAccessRulesList(): self
    {
        $new = clone $this;
        $new->accessRulesList = [];

        return $new;
    }
}
