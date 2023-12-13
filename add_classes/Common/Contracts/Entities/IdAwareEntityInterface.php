<?php

namespace App\Common\Contracts\Entities;

interface IdAwareEntityInterface
{
    /**
     * Returns the instance ID value.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the instance with provided ID value.
     *
     * @param int $id
     *
     * @return static
     */
    public function withId($id);

    /**
     * Returns the instance without ID value.
     *
     * @return static
     */
    public function withoutId();
}
