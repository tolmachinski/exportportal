<?php

declare(strict_types=1);

namespace App\Users;

use App\Users\Contracts\PersonInterface;

final class Person implements PersonInterface
{
    use PersonTrait;
    use GroupMemberTrait;

    /**
     * Creates instance of the person.
     */
    public function __construct(?int $id = null, ?PersonalName $name = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->groupName = null;
        $this->groupType = null;
        $this->groupAlias = null;
    }
}
