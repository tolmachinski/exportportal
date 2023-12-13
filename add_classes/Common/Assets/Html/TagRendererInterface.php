<?php

declare(strict_types=1);

namespace App\Common\Assets\Html;

interface TagRendererInterface
{
    /**
     * Get the list of rendered scripts.
     */
    public function getRenderedScripts(): array;

    /**
     * Get the list of rendered styles.
     */
    public function getRenderedStyles(): array;

    /**
     * Renders the script tags for given entry.
     */
    public function renderScriptTags(
        string $entryName,
        string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): string;

    /**
     * Renders the link tags for given entry.
     */
    public function renderLinkTags(
        string $entryName,
        string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = array(),
        bool $inline = false,
        bool $escape = true
    ): string;
}
