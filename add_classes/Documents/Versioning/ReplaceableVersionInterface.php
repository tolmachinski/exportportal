<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

interface ReplaceableVersionInterface
{
    /**
     * Indicates which version it replaces.
     *
     * @return static
     */
    public function replacesVersion(VersionInterface $version);

    /**
     * Checks if replacement attempt number exists.
     */
    public function hasReplacementAttempt(): bool;

    /**
     * Returns the current replacement attempt number.
     */
    public function getReplacementAttempt(): ?int;

    /**
     * Returns an instance with the specified replacement attempt number.
     *
     * @return static
     */
    public function withReplacementAttempt(int $replacementAttempt);

    /**
     * Return an instance without replacement attempt number.
     *
     * @return static
     */
    public function withoutReplacementAttempt();

    /**
     * Checks if version creation date of the original exists.
     */
    public function hasOriginalCreationDate(): bool;

    /**
     * Returns the date when original version was created.
     */
    public function getOriginalCreationDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified date of creation of the original version.
     *
     * @return static
     */
    public function withOriginalCreationDate(DateTimeImmutable $date);

    /**
     * Return an instance without date of creation of the original version.
     *
     * @return static
     */
    public function withoutOriginalCreationDate();
}
