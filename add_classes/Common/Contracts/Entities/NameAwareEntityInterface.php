<?php

namespace App\Common\Contracts\Entities;

interface NameAwareEntityInterface
{
    /**
     * Returns the name value.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the instance with provided name value.
     *
     * @param string $name
     *
     * @return static
     */
    public function withName($name);

    /**
     * Returns the instance without name value.
     *
     * @return static
     */
    public function withoutName();
}
