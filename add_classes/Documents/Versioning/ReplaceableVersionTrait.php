<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

trait ReplaceableVersionTrait
{
    /**
     * The current replacement attempt.
     *
     * @var int
     */
    private $replacementAttempt;

    /**
     * The original version creation date.
     *
     * @var null|DateTimeImmutable
     */
    private $originalCreationDate;

    /**
     * Indicates which version it replaces.
     *
     * @param ReplaceableVersionInterface|VersionInterface $version
     *
     * @return static
     */
    public function replacesVersion(VersionInterface $version)
    {
        return $this
            ->withReplacementAttempt((int) $version->getReplacementAttempt() + 1)
            ->withOriginalCreationDate(
                $version instanceof ReplaceableVersionInterface && $version->hasOriginalCreationDate()
                    ? $version->getOriginalCreationDate()
                    : $version->getCreationDate()
            )
        ;
    }

    /**
     * Checks if replacement attempt number exists.
     */
    public function hasReplacementAttempt(): bool
    {
        return null !== $this->replacementAttempt;
    }

    /**
     * Returns the current replacement attempt number.
     */
    public function getReplacementAttempt(): ?int
    {
        return $this->replacementAttempt;
    }

    /**
     * Returns an instance with the specified replacement attempt number.
     *
     * @return static
     */
    public function withReplacementAttempt(int $replacementAttempt)
    {
        $new = clone $this;
        $new->replacementAttempt = $replacementAttempt;

        return $new;
    }

    /**
     * Return an instance without replacement attempt number.
     *
     * @return static
     */
    public function withoutReplacementAttempt()
    {
        $new = clone $this;
        $new->rejectionDate = null;

        return $new;
    }

    /**
     * Checks if version creation date of the original exists.
     */
    public function hasOriginalCreationDate(): bool
    {
        return null !== $this->originalCreationDate;
    }

    /**
     * Returns the date when original version was created.
     */
    public function getOriginalCreationDate(): ?DateTimeImmutable
    {
        return $this->originalCreationDate;
    }

    /**
     * Returns an instance with the specified date of creation of the original version.
     *
     * @return static
     */
    public function withOriginalCreationDate(DateTimeImmutable $date)
    {
        $new = clone $this;
        $new->originalCreationDate = $date;

        return $new;
    }

    /**
     * Return an instance without date of creation of the original version.
     *
     * @return static
     */
    public function withoutOriginalCreationDate()
    {
        $new = clone $this;
        $new->originalCreationDate = null;

        return $new;
    }
}
