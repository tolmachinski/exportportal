<?php

namespace App\Common\Cache\Contracts;

interface PoolFactoryInterface
{
    /**
     * Creates a cache pool.
     *
     * @param array $config
     * @param bool  $isPsr
     *
     * @return mixed
     */
    public function make(array $config, $isPsr = false);
}
