<?php

declare(strict_types=1);

namespace App\Common\Assets\Html;

interface WebLinkedTagRendererInterface extends TagRendererInterface
{
    /**
     * Change the preload importance.
     */
    public function changeImportance(?string $importance): self;

    /**
     * Change the NOPUSH mode.
     */
    public function changeNopushMode(bool $nopush): self;
}
