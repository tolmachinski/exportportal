<?php

declare(strict_types=1);

namespace App\Plugins\EPDocs;

interface ContextBuilderInterface
{
    /**
     * Returns the context.
     *
     * @return array
     */
    public function buildContext(): ContextBuilderInterface;

    /**
     * Returns the prepared context.
     */
    public function getContext(): array;
}
