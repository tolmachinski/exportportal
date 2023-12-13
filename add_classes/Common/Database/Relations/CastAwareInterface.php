<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

interface CastAwareInterface
{
    /**
     * Enables the native cast for relation.
     *
     * @return static
     */
    public function enableNativeCast(): self;

    /**
     * Disables the native cast for relation.
     *
     * @return static
     */
    public function disableNativeCast(): self;

    /**
     * Determines if native cast is enabled for this relation.
     */
    public function isNativeCastEnabled(): bool;
}
