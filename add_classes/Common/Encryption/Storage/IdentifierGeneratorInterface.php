<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

interface IdentifierGeneratorInterface
{
    /**
     * Creates identifier from the provided parts.
     *
     * @param string ...$parts
     *
     * @return string
     */
    public function createIdentifier(string ...$parts): string;

    /**
     * Mangles the provided part of identifier.
     *
     * @param string $part
     *
     * @return string
     */
    public function manglePart(string $part): string;
}
