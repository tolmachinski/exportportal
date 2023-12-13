<?php

declare(strict_types=1);

namespace App\Common\Encryption\Storage;

final class KeyFilePathGenerator implements IdentifierGeneratorInterface
{
    const HASH_ALGO = 'sha256';

    /**
     * Creates identifier from the provided parts.
     *
     * @param string ...$parts
     *
     * @return string
     */
    public function createIdentifier(string ...$parts): string
    {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Mangles the provided part of identifier.
     *
     * @param string $part
     *
     * @return string
     */
    public function manglePart(string $part): string
    {
        return hash(static::HASH_ALGO, $part);
    }
}
