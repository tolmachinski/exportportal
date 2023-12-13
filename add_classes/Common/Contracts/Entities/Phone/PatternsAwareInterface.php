<?php

declare(strict_types=1);

namespace App\Common\Contracts\Entities\Phone;

interface PatternsAwareInterface
{
    const PATTERN_GENERAL = 1;
    const PATTERN_INTERNATIONAL_MASK = 2;

    /**
     * Returns the pattern by provided type.
     */
    public function getPattern(?int $type): ?string;

    /**
     * Returns the phone patterns.
     */
    public function getPatterns(): array;

    /**
     * Returns the instance with provided phone patterns.
     *
     * @return static
     */
    public function withPatterns(array $phonePatterns): PatternsAwareInterface;

    /**
     * Returns the instance without phone patterns.
     *
     * @return static
     */
    public function withoutPatterns(): PatternsAwareInterface;
}
