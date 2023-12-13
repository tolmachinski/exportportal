<?php

declare(strict_types=1);

namespace App\Common\Assets;

interface ServiceResetInterface
{
    /**
     * Resets the list of returned files.
     */
    public function resetFiles(): void;

    /**
     * Reset the state of the service.
     */
    public function reset(): void;
}
