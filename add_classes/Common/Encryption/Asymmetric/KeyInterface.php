<?php

declare(strict_types=1);

namespace App\Common\Encryption\Asymmetric;

interface KeyInterface
{
    /**
     * Returns the key raw material.
     *
     * @return string
     */
    public function getRawKeyMaterial(): string;
}
