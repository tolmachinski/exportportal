<?php

namespace App\Common\Contracts;

interface Jsonable
{
    /**
     * Convert the instance to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0);
}
