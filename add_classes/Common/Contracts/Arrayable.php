<?php

namespace App\Common\Contracts;

interface Arrayable
{
    /**
     * Convert the instance to an array.
     *
     * @return array
     */
    public function toArray();
}
